<?php

namespace Tests\Feature;

use App\Http\Controllers\Admin\AccountReportController;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AccountReportsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->create(['role' => 'superadmin']));
    }

    public function test_every_accounts_report_renders(): void
    {
        $reports = array_keys(AccountReportController::FINANCIAL_REPORTS + AccountReportController::OPERATIONAL_REPORTS);

        foreach ($reports as $report) {
            $this->get(route('admin.accounts.reports', $report))
                ->assertOk()
                ->assertSee(AccountReportController::FINANCIAL_REPORTS[$report] ?? AccountReportController::OPERATIONAL_REPORTS[$report]);
        }
    }
}
