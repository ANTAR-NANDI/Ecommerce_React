<?php

namespace Tests\Feature;

use App\Models\AccountCoa;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ChartOfAccountsHierarchyTest extends TestCase
{
    use RefreshDatabase;

    public function test_an_asset_group_can_contain_a_furniture_ledger_account(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'superadmin']));
        $assets = AccountCoa::where('code', '100')->firstOrFail();

        $this->post(route('admin.accounts.coa.store'), ['parent_id' => $assets->id, 'head_name' => 'Fixed Assets', 'is_group' => 1])
            ->assertSessionHasNoErrors()->assertRedirect();
        $fixedAssets = AccountCoa::where('head_name', 'Fixed Assets')->sole();
        $this->assertTrue($fixedAssets->is_group);
        $this->assertSame('asset', $fixedAssets->account_type);

        $this->post(route('admin.accounts.coa.store'), ['parent_id' => $fixedAssets->id, 'head_name' => 'Furniture & Fixtures'])
            ->assertSessionHasNoErrors();
        $furniture = AccountCoa::where('head_name', 'Furniture & Fixtures')->sole();
        $this->assertFalse($furniture->is_group);
        $this->assertSame($fixedAssets->id, $furniture->parent_id);
        $this->assertSame('asset', $furniture->account_type);

        $this->get(route('admin.accounts.coa'))->assertOk()->assertSee('Fixed Assets')->assertSee('Furniture &amp; Fixtures', false);
    }
}
