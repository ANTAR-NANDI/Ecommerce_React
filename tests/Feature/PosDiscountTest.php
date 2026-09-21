<?php

namespace Tests\Feature;

use App\Models\AccountCoa;
use App\Models\AccountTransaction;
use App\Models\Customer;
use App\Models\PaymentMethod;
use App\Models\PosOrder;
use App\Models\Product;
use App\Models\User;
use App\Models\Warehouse;
use App\Models\WarehouseProductStock;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PosDiscountTest extends TestCase
{
    use RefreshDatabase;

    private function sale(array $overrides = []): array
    {
        $this->actingAs(User::factory()->create(['role' => 'superadmin']));
        $warehouse = Warehouse::create(['name' => 'Test warehouse', 'code' => 'TEST']);
        $product = Product::create(['name' => 'Test product', 'slug' => 'test-product', 'sku' => 'TEST', 'short_description' => 'Test', 'buying_price' => 50, 'selling_price' => 100, 'stock_quantity' => 10]);
        WarehouseProductStock::create(['warehouse_id' => $warehouse->id, 'product_id' => $product->id, 'quantity' => 10]);
        $method = PaymentMethod::firstOrCreate(['name' => 'Cash in Hand'], ['account_coa_id' => AccountCoa::where('code', '10011')->firstOrFail()->id]);

        return array_replace([
            'warehouse_id' => $warehouse->id, 'payment_method_id' => $method->id, 'status' => 'completed',
            'items' => [['product_id' => $product->id, 'product_name' => $product->name, 'quantity' => 2, 'unit_price' => 100]],
        ], $overrides);
    }

    public function test_fixed_discount_updates_invoice_stock_and_balanced_accounting(): void
    {
        $data = $this->sale(['discount_type' => 'fixed', 'discount_value' => 25, 'total' => 1, 'discount_amount' => 199]);
        $this->post(route('admin.pos.orders.save'), $data)->assertSessionHasNoErrors()->assertRedirect();
        $order = PosOrder::sole();
        $this->assertSame('200.00', $order->subtotal);
        $this->assertSame('25.00', $order->discount_amount);
        $this->assertSame('175.00', $order->total);
        $this->assertEquals(8, WarehouseProductStock::sole()->quantity);
        $entries = AccountTransaction::where('pos_order_id', $order->id)->get();
        $this->assertCount(4, $entries);
        $this->assertEquals(275, $entries->where('entry_type', 'debit')->sum('amount'));
        $this->assertEquals(275, $entries->where('entry_type', 'credit')->sum('amount'));
        $this->assertDatabaseHas('pos_order_items', ['pos_order_id' => $order->id, 'unit_cost' => 50]);
        $this->assertDatabaseHas('account_transactions', [
            'pos_order_id' => $order->id,
            'account_coa_id' => AccountCoa::where('code', '5001')->value('id'),
            'entry_type' => 'debit',
            'amount' => 100,
        ]);
        $this->assertDatabaseHas('account_transactions', [
            'pos_order_id' => $order->id,
            'account_coa_id' => AccountCoa::where('code', '10014')->value('id'),
            'entry_type' => 'credit',
            'amount' => 100,
        ]);
        $this->get(route('admin.pos.invoice', $order))->assertOk()->assertSee('Subtotal')->assertSee('Discount')->assertSee('$25.00')->assertSee('$175.00');
    }

    public function test_percentage_discount_survives_draft_and_posts_net_customer_due(): void
    {
        $data = $this->sale(['status' => 'draft', 'discount_type' => 'percentage', 'discount_value' => 12.5]);
        $data['items'][0]['unit_price'] = 99.99;
        $this->post(route('admin.pos.orders.save'), $data)->assertSessionHasNoErrors()->assertRedirect();
        $order = PosOrder::sole();
        $this->assertSame('199.98', $order->subtotal);
        $this->assertSame('25.00', $order->discount_amount);
        $this->assertSame('174.98', $order->total);
        $this->assertEquals(10, WarehouseProductStock::sole()->quantity);
        $this->assertDatabaseCount('account_transactions', 0);
        $this->get(route('admin.pos.index', ['draft' => $order->id]))->assertOk()
            ->assertSee('id="pos-discount-type"', false)->assertSee('value="12.50"', false);

        $customer = Customer::create(['first_name' => 'Discount customer', 'phone' => '01800000001', 'password' => 'password']);
        unset($data['payment_method_id']);
        $data['status'] = 'completed';
        $data['payment_method'] = 'Due';
        $data['customer_id'] = $customer->id;
        $this->put(route('admin.pos.drafts.update', $order), $data)->assertSessionHasNoErrors()->assertRedirect();
        $this->assertSame('174.98', $order->fresh()->total);
        $head = AccountCoa::where('customer_id', $customer->id)->firstOrFail();
        $this->assertDatabaseHas('account_transactions', ['pos_order_id' => $order->id, 'account_coa_id' => $head->id, 'entry_type' => 'debit', 'amount' => 174.98]);
        $this->assertDatabaseMissing('account_transactions', [
            'pos_order_id' => $order->id,
            'account_coa_id' => AccountCoa::where('code', '4001')->value('id'),
            'customer_id' => $customer->id,
        ]);
        $this->assertEquals(8, WarehouseProductStock::sole()->quantity);
    }

    public function test_invalid_discounts_do_not_create_sales_or_change_stock(): void
    {
        $data = $this->sale();
        foreach ([['fixed', -1], ['fixed', 200.01], ['percentage', 100.01], ['percentage', -5], ['fixed', 'abc'], ['fixed', 1.234], ['invalid', 10]] as [$type, $value]) {
            $this->post(route('admin.pos.orders.save'), $data + ['discount_type' => $type, 'discount_value' => $value])
                ->assertSessionHasErrors($type === 'invalid' ? 'discount_type' : 'discount_value');
        }
        $this->assertDatabaseCount('pos_orders', 0);
        $this->assertDatabaseCount('account_transactions', 0);
        $this->assertEquals(10, WarehouseProductStock::sole()->quantity);
    }

    public function test_sale_without_discount_keeps_original_total(): void
    {
        $this->post(route('admin.pos.orders.save'), $this->sale())->assertSessionHasNoErrors()->assertRedirect();
        $this->assertSame('200.00', PosOrder::sole()->total);
        $this->assertSame('0.00', PosOrder::sole()->discount_amount);
    }

    public function test_discount_can_be_removed_from_draft(): void
    {
        $data = $this->sale(['status' => 'draft', 'discount_type' => 'fixed', 'discount_value' => 50]);
        $this->post(route('admin.pos.orders.save'), $data)->assertSessionHasNoErrors();
        $order = PosOrder::sole();
        $data['discount_value'] = 0;
        $this->put(route('admin.pos.drafts.update', $order), $data)->assertSessionHasNoErrors();
        $this->assertSame('0.00', $order->fresh()->discount_amount);
        $this->assertSame('200.00', $order->fresh()->total);
    }

    public function test_full_percentage_discount_never_produces_negative_total(): void
    {
        $this->post(route('admin.pos.orders.save'), $this->sale(['discount_type' => 'percentage', 'discount_value' => 100]))->assertSessionHasNoErrors();
        $this->assertSame('0.00', PosOrder::sole()->total);
        $this->assertSame('200.00', PosOrder::sole()->discount_amount);
    }
}
