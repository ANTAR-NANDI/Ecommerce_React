<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardChartTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_dashboard_shows_financial_charts(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'superadmin']));

        $this->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Expense composition')
            ->assertSee('Profit and loss waterfall')
            ->assertSee('Sales and purchase trend')
            ->assertSee('Profit and loss overview')
            ->assertViewHas('chartData', fn (array $chartData) => isset($chartData['monthly'], $chartData['performance'], $chartData['kpis'], $chartData['expense_pie'], $chartData['waterfall']));
    }
}
