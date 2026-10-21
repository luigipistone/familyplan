<?php

namespace Tests\Unit;

use App\Services\PrintCostCalculator;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class PrintCostCalculatorTest extends TestCase
{
    private PrintCostCalculator $calculator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->calculator = new PrintCostCalculator;
    }

    public function test_simple_single_color_print(): void
    {
        $r = $this->calculator->calculate($this->base());
        $this->assertSame(200, $r['filament']['model']);
        $this->assertSame(18, $r['energy_cents']);
        $this->assertSame(300, $r['machine_cents']);
    }

    public function test_multiple_filaments_support_and_purge_are_summed(): void
    {
        $d = $this->base();
        $d['filaments'][0]['support_grams'] = 10;
        $d['filaments'][0]['purge_grams'] = 30;
        $d['filaments'][] = ['spool_cost' => 30, 'spool_grams' => 1000, 'model_grams' => 20, 'support_grams' => 0, 'purge_grams' => 40, 'waste_percentage' => 0];
        $r = $this->calculator->calculate($d);
        $this->assertGreaterThan(0, $r['filament']['support']);
        $this->assertGreaterThan($r['filament']['model'], $r['filament']['total']);
    }

    public function test_quantity_multiplies_variable_costs_but_not_fixed_order_costs(): void
    {
        $d = $this->base();
        $d['quantity'] = 3;
        $d['labor'] = [['minutes' => 60, 'hourly_rate' => 12, 'scope' => 'fixed_order'], ['minutes' => 30, 'hourly_rate' => 12, 'scope' => 'per_piece']];
        $d['additional_costs'] = [['quantity' => 1, 'unit_cost' => 5, 'scope' => 'fixed_order']];
        $r = $this->calculator->calculate($d);
        $this->assertSame(3000, $r['labor_cents']);
        $this->assertSame(500, $r['additional_cents']);
    }

    public function test_margin_and_markup_are_different(): void
    {
        $markup = $this->base();
        $markup['pricing_percentage'] = 30;
        $margin = $markup;
        $margin['pricing_mode'] = 'margin';
        $this->assertGreaterThan($this->calculator->calculate($markup)['final_price_cents'], $this->calculator->calculate($margin)['final_price_cents']);
    }

    public function test_failure_risk_uses_probability_formula(): void
    {
        $d = $this->base();
        $d['failure_percentage'] = 10;
        $r = $this->calculator->calculate($d);
        $this->assertSame((int) round($r['base_cost_cents'] / .9), $r['total_cost_cents']);
    }

    public function test_vat_and_rounding_are_applied(): void
    {
        $d = $this->base();
        $d['vat_percentage'] = 22;
        $d['rounding'] = '99';
        $r = $this->calculator->calculate($d);
        $this->assertGreaterThan(0, $r['vat_cents']);
        $this->assertSame(99, $r['final_price_cents'] % 100);
    }

    public function test_null_values_are_treated_as_zero(): void
    {
        $r = $this->calculator->calculate(['quantity' => 1, 'filaments' => [], 'pricing_mode' => 'markup']);
        $this->assertSame(0, $r['final_price_cents']);
    }

    #[DataProvider('invalidData')]
    public function test_invalid_values_are_rejected(array $data): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->calculator->calculate(array_replace_recursive($this->base(), $data));
    }

    public static function invalidData(): array
    {
        return [
            'negative' => [['machine_rate_hour' => -1]],
            'invalid margin' => [['pricing_mode' => 'margin', 'pricing_percentage' => 100]],
            'invalid failure' => [['failure_percentage' => 91]],
            'zero spool' => [['filaments' => [['spool_cost' => 20, 'spool_grams' => 0, 'model_grams' => 10]]]],
        ];
    }

    private function base(): array
    {
        return ['quantity' => 1, 'failure_percentage' => 0, 'print_hours' => 2, 'print_minutes' => 0, 'average_watts' => 150, 'energy_cost_kwh' => .60, 'machine_rate_hour' => 1.50, 'filaments' => [['spool_cost' => 20, 'spool_grams' => 1000, 'model_grams' => 100, 'support_grams' => 0, 'purge_grams' => 0, 'waste_percentage' => 5]], 'labor' => [], 'additional_costs' => [], 'pricing_mode' => 'markup', 'pricing_percentage' => 30, 'vat_percentage' => 0, 'vat_included' => false, 'rounding' => 'cent'];
    }
}
