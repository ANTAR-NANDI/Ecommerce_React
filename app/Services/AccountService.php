<?php

namespace App\Services;

use App\Models\AccountCoa;
use App\Models\AccountTransaction;
use App\Models\Customer;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AccountService
{
    public function syncProfileHeads(): void
    {
        Supplier::query()->each(fn (Supplier $supplier) => $this->ensureSupplierHead($supplier));
        Customer::query()->each(fn (Customer $customer) => $this->ensureCustomerHead($customer));
        User::query()->each(fn (User $employee) => $this->ensureEmployeeHead($employee));
    }

    public function ensureSupplierHead(Supplier $supplier): AccountCoa
    {
        return $this->ensureEntityHead('supplier', $supplier->id, $supplier->name, '20011', 'liability');
    }

    public function ensureCustomerHead(Customer $customer): AccountCoa
    {
        return $this->ensureEntityHead('customer', $customer->id, $customer->full_name ?: $customer->phone, '10013', 'asset');
    }

    public function ensureEmployeeHead(User $employee): AccountCoa
    {
        return $this->ensureEntityHead('employee', $employee->id, $employee->name, '10015', 'asset');
    }

    public function createChild(AccountCoa $parent, string $name): AccountCoa
    {
        if (! $parent->is_group) {
            throw ValidationException::withMessages(['parent_id' => 'Choose a group account to create a sub-account.']);
        }

        return DB::transaction(function () use ($parent, $name) {
            $parent = AccountCoa::lockForUpdate()->findOrFail($parent->id);
            $sequence = $parent->children()->lockForUpdate()->count() + 1;

            return AccountCoa::create([
                'parent_id' => $parent->id,
                'code' => (string) (((int) $parent->code * 10) + $sequence),
                'head_name' => $name,
                'account_type' => $parent->account_type,
                'is_group' => false,
            ]);
        });
    }

    public function postVoucher(array $data, ?int $userId = null): string
    {
        if ($data['debit_account_id'] === $data['credit_account_id']) {
            throw ValidationException::withMessages(['credit_account_id' => 'Debit and credit accounts must be different.']);
        }
        $amount = round((float) $data['amount'], 2);
        $voucherNo = strtoupper($data['voucher_type']).'-'.now()->format('YmdHis').'-'.random_int(100, 999);
        DB::transaction(function () use ($data, $amount, $voucherNo, $userId) {
            $accounts = AccountCoa::whereKey([$data['debit_account_id'], $data['credit_account_id']])->get()->keyBy('id');
            foreach ([['account_coa_id' => $data['debit_account_id'], 'entry_type' => 'debit'], ['account_coa_id' => $data['credit_account_id'], 'entry_type' => 'credit']] as $entry) {
                $account = $accounts->get($entry['account_coa_id']);
                AccountTransaction::create($entry + [
                    'voucher_no' => $voucherNo, 'voucher_type' => $data['voucher_type'], 'transaction_date' => $data['transaction_date'],
                    'amount' => $amount, 'ledger_comment' => $data['ledger_comment'] ?? null,
                    'supplier_id' => $data['supplier_id'] ?? $account?->supplier_id, 'customer_id' => $data['customer_id'] ?? $account?->customer_id,
                    'employee_id' => $data['employee_id'] ?? null, 'purchase_id' => $data['purchase_id'] ?? null,
                    'sale_id' => $data['sale_id'] ?? null, 'pos_order_id' => $data['pos_order_id'] ?? null, 'ecommerce_order_id' => $data['ecommerce_order_id'] ?? null, 'created_by' => $userId,
                ]);
            }
        });

        return $voucherNo;
    }

    /**
     * Post a balanced multi-line entry, used for system-generated opening balances.
     * Each entry must contain account_coa_id, entry_type, and amount.
     */
    public function postEntries(array $entries, array $context, ?int $userId = null): void
    {
        $debits = collect($entries)->where('entry_type', 'debit')->sum('amount');
        $credits = collect($entries)->where('entry_type', 'credit')->sum('amount');

        if (round((float) $debits, 2) !== round((float) $credits, 2) || $debits <= 0) {
            throw ValidationException::withMessages(['items' => 'Ledger entries must have equal debit and credit totals.']);
        }

        DB::transaction(function () use ($entries, $context, $userId) {
            foreach ($entries as $entry) {
                AccountTransaction::create([
                    'voucher_no' => $context['voucher_no'],
                    'voucher_type' => $context['voucher_type'] ?? 'journal',
                    'transaction_date' => $context['transaction_date'],
                    'account_coa_id' => $entry['account_coa_id'],
                    'entry_type' => $entry['entry_type'],
                    'amount' => round((float) $entry['amount'], 2),
                    'ledger_comment' => $context['ledger_comment'] ?? null,
                    'supplier_id' => $context['supplier_id'] ?? null,
                    'customer_id' => $context['customer_id'] ?? null,
                    'purchase_id' => $context['purchase_id'] ?? null,
                    'ecommerce_order_id' => $context['ecommerce_order_id'] ?? null,
                    'pos_order_id' => $context['pos_order_id'] ?? null,
                    'created_by' => $userId,
                ]);
            }
        });
    }

    private function ensureEntityHead(string $column, int $id, string $name, string $parentCode, string $type): AccountCoa
    {
        $existing = AccountCoa::where($column.'_id', $id)->first();
        if ($existing) {
            return $existing;
        }
        $parent = AccountCoa::where('code', $parentCode)->firstOrFail();

        return DB::transaction(function () use ($column, $id, $name, $parent, $type) {
            $existing = AccountCoa::where($column.'_id', $id)->lockForUpdate()->first();
            if ($existing) {
                return $existing;
            }
            $lockedParent = AccountCoa::lockForUpdate()->findOrFail($parent->id);
            $sequence = $lockedParent->children()->lockForUpdate()->count() + 1;

            return AccountCoa::create([
                'parent_id' => $lockedParent->id, 'code' => (string) (((int) $lockedParent->code * 10) + $sequence),
                'head_name' => $name, 'account_type' => $type, 'is_group' => false, $column.'_id' => $id,
            ]);
        });
    }
}
