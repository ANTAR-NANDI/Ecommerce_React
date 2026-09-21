<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('account_transactions')
            ->select('voucher_no')
            ->whereNotNull('voucher_no')
            ->where(fn ($query) => $query->whereNull('ledger_comment')->orWhere('ledger_comment', ''))
            ->distinct()
            ->pluck('voucher_no')
            ->chunk(100)
            ->each(function ($voucherNos): void {
                $entries = DB::table('account_transactions')
                    ->join('account_coas', 'account_transactions.account_coa_id', '=', 'account_coas.id')
                    ->whereIn('account_transactions.voucher_no', $voucherNos)
                    ->select('account_transactions.id', 'account_transactions.voucher_no', 'account_transactions.voucher_type', 'account_transactions.entry_type', 'account_transactions.ledger_comment', 'account_coas.head_name')
                    ->get()
                    ->groupBy('voucher_no');

                foreach ($entries as $voucherEntries) {
                    $debit = $voucherEntries->firstWhere('entry_type', 'debit')?->head_name ?? 'selected account';
                    $credit = $voucherEntries->firstWhere('entry_type', 'credit')?->head_name ?? 'selected account';
                    $narration = ucfirst($voucherEntries->first()->voucher_type)." entry: Debit {$debit}; Credit {$credit}";

                    foreach ($voucherEntries->filter(fn ($entry) => blank($entry->ledger_comment)) as $entry) {
                        DB::table('account_transactions')->where('id', $entry->id)->update(['ledger_comment' => $narration]);
                    }
                }
            });

        DB::table('account_transactions')
            ->whereNull('voucher_no')
            ->where(fn ($query) => $query->whereNull('ledger_comment')->orWhere('ledger_comment', ''))
            ->update(['ledger_comment' => 'System generated accounting entry']);
    }

    public function down(): void
    {
        // Narrations describe historical transactions and should remain available on rollback.
    }
};
