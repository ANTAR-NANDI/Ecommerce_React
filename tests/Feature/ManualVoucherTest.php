<?php

namespace Tests\Feature;

use App\Models\AccountCoa;
use App\Models\AccountTransaction;
use App\Models\Customer;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ManualVoucherTest extends TestCase
{
    use RefreshDatabase;

    public function test_manual_vouchers_derive_party_links_and_ignore_removed_reference_inputs(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'superadmin']));
        $supplier = Supplier::create(['name' => 'Test supplier', 'phone' => '01700000000']);
        $customer = Customer::create(['first_name' => 'Test customer', 'phone' => '01800000000', 'password' => 'test-password']);
        $cash = AccountCoa::where('code', '10011')->firstOrFail();

        foreach ([
            ['debit', 'supplier_id', $supplier->id, 'liability'],
            ['credit', 'customer_id', $customer->id, 'asset'],
        ] as [$type, $partyKey, $partyId, $accountType]) {
            $head = AccountCoa::create(['code' => 'TEST-'.$type, 'head_name' => 'Test '.$type, 'account_type' => $accountType, $partyKey => $partyId]);
            $this->post(route('admin.accounts.vouchers.store'), [
                'voucher_type' => $type,
                'transaction_date' => '2026-09-20',
                'amount' => 125,
                'debit_account_id' => $type === 'debit' ? $head->id : $cash->id,
                'credit_account_id' => $type === 'credit' ? $head->id : $cash->id,
                'supplier_id' => 999999,
                'customer_id' => 999999,
                'purchase_id' => 999999,
            ])->assertSessionHasNoErrors()->assertRedirect();

            $entries = AccountTransaction::where('voucher_type', $type)->get();
            $this->assertCount(2, $entries);
            $this->assertEquals(125, $entries->where('entry_type', 'debit')->sum('amount'));
            $this->assertEquals(125, $entries->where('entry_type', 'credit')->sum('amount'));
            $this->assertEquals($partyId, $entries->firstWhere('account_coa_id', $head->id)->{$partyKey});
            $this->assertNull($entries->firstWhere('account_coa_id', $cash->id)->{$partyKey});
            foreach ($entries as $entry) {
                $this->assertNull($entry->purchase_id);
            }
        }
    }

    public function test_all_manual_voucher_forms_omit_redundant_reference_fields(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'superadmin']));
        foreach (['debit', 'credit', 'journal', 'contra'] as $type) {
            $this->get(route('admin.accounts.vouchers', ['type' => $type]))
                ->assertOk()
                ->assertSee('name="debit_account_id"', false)
                ->assertSee('name="credit_account_id"', false)
                ->assertDontSee('name="supplier_id"', false)
                ->assertDontSee('name="customer_id"', false)
                ->assertDontSee('name="purchase_id"', false);
        }
    }
}
