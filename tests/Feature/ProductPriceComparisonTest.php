<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\Purchase;
use App\Models\Supplier;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductPriceComparisonTest extends TestCase
{
    use RefreshDatabase;

    public function test_comparison_uses_historical_costs_and_inclusive_purchase_dates(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'superadmin']));
        $warehouse = Warehouse::create(['name' => 'Test warehouse', 'code' => 'TEST']);
        $product = Product::create(['name' => 'Mouse', 'slug' => 'mouse', 'sku' => 'MOUSE', 'short_description' => 'Test mouse', 'buying_price' => 999, 'selling_price' => 1200]);
        $other = Product::create(['name' => 'Keyboard', 'slug' => 'keyboard', 'sku' => 'KEYBOARD', 'short_description' => 'Test keyboard', 'buying_price' => 30, 'selling_price' => 50]);
        foreach ([
            ['FIRST', '2026-09-18', 'received', 18, $product],
            ['SECOND', '2026-09-20', 'received', 15, $product],
            ['EARLY', '2026-09-17', 'received', 12, $product],
            ['LATE', '2026-09-21', 'received', 14, $product],
            ['DRAFT', '2026-09-19', 'draft', 5, $product],
            ['ORDERED', '2026-09-19', 'ordered', 6, $product],
            ['OTHER', '2026-09-19', 'received', 30, $other],
        ] as [$reference, $date, $status, $cost, $itemProduct]) {
            $supplier = Supplier::create(['name' => 'Supplier '.$reference, 'phone' => '01700000000']);
            $purchase = Purchase::create([
                'purchase_number' => $reference, 'warehouse_id' => $warehouse->id,
                'supplier_id' => $supplier->id, 'supplier_name' => $supplier->name,
                'purchase_date' => $date, 'status' => $status, 'subtotal' => $cost * 2, 'total' => $cost * 2,
            ]);
            // Historical purchase date must be used, not today's creation timestamp.
            $purchase->items()->create(['product_id' => $itemProduct->id, 'product_name' => $itemProduct->name, 'quantity' => 2, 'unit_cost' => $cost, 'line_total' => $cost * 2]);
        }

        $url = route('admin.accounts.reports', 'product-price-comparison');
        $response = $this->get($url.'?'.http_build_query(['from_date' => '2026-09-18', 'to_date' => '2026-09-20', 'product_id' => $product->id]));
        $response->assertOk()->assertSee('Print')->assertSee('Export PDF')
            ->assertViewHas('result', function (array $result) {
                $rows = $result['rows']->values()->all();
                return count($rows) === 2
                    && $rows[0] === ['20 Sep 2026', 'Supplier SECOND', 'Mouse', 'MOUSE', 'SECOND', '2.000', '$15.00', '$30.00']
                    && $rows[1] === ['18 Sep 2026', 'Supplier FIRST', 'Mouse', 'MOUSE', 'FIRST', '2.000', '$18.00', '$36.00'];
            });

        $this->get($url)->assertOk()->assertViewHas('result', fn ($result) => $result['rows']->count() === 5);
        $this->get($url.'?from_date=2026-09-22')->assertOk()->assertSee('No matching records found.');
        $this->get($url.'?from_date=2026-09-20&to_date=2026-09-18')->assertSessionHasErrors('to_date');
        $this->get($url.'?product_id=999999')->assertSessionHasErrors('product_id');
    }
}
