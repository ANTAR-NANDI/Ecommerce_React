<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\Supplier;
use App\Models\User;
use App\Models\Warehouse;
use App\Models\WarehouseProductStock;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PurchaseStockPrecisionTest extends TestCase
{
    use RefreshDatabase;

    public function test_received_purchase_supports_fractional_inventory_quantities(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'superadmin']));
        $warehouse = Warehouse::create(['name' => 'Main warehouse', 'code' => 'MAIN']);
        $supplier = Supplier::create(['name' => 'Fresh supply', 'phone' => '01700000000']);
        $product = Product::create([
            'name' => 'Bulk rice',
            'slug' => 'bulk-rice',
            'sku' => 'BULK-RICE',
            'short_description' => 'Rice sold by weight.',
            'buying_price' => 50,
            'selling_price' => 70,
        ]);

        $this->post(route('admin.purchases.store'), [
            'warehouse_id' => $warehouse->id,
            'supplier_id' => $supplier->id,
            'purchase_date' => '2026-10-08',
            'status' => 'received',
            'items' => [[
                'product_id' => $product->id,
                'quantity' => 0.5,
                'unit_cost' => 50,
            ]],
        ])->assertRedirect(route('admin.purchases.index'));

        $this->assertSame('0.500', $product->fresh()->stock_quantity);
        $this->assertSame('0.500', WarehouseProductStock::query()
            ->where('warehouse_id', $warehouse->id)
            ->where('product_id', $product->id)
            ->value('quantity'));
    }
}
