<?php

namespace App\Services;

use InvalidArgumentException;

class PrintCostCalculator
{
    public function calculate(array $data): array
    {
        $quantity = max(1, (int) ($data['quantity'] ?? 1));
        $risk = $this->percentage($data['failure_percentage'] ?? 0, 90, 'Percentuale fallimento non valida.');
        $filament = ['model' => 0, 'support' => 0, 'purge' => 0, 'waste' => 0, 'total' => 0];

        foreach ($data['filaments'] ?? [] as $row) {
            $spoolGrams = $this->nonNegative($row['spool_grams'] ?? 0);
            $spoolCents = $this->money($row['spool_cost'] ?? 0);
            if ($spoolCents > 0 && $spoolGrams <= 0) {
                throw new InvalidArgumentException('Il peso della bobina deve essere maggiore di zero.');
            }
            $costPerGram = $spoolGrams > 0 ? $spoolCents / $spoolGrams : 0;
            $wasteFactor = 1 + $this->percentage($row['waste_percentage'] ?? 0, 1000, 'Scarto non valido.') / 100;
            foreach (['model_grams' => 'model', 'support_grams' => 'support', 'purge_grams' => 'purge'] as $field => $key) {
                $base = $this->nonNegative($row[$field] ?? 0) * $quantity * $costPerGram;
                $filament[$key] += (int) round($base);
                $filament['waste'] += (int) round($base * ($wasteFactor - 1));
            }
        }
        $filament['total'] = $filament['model'] + $filament['support'] + $filament['purge'] + $filament['waste'];

        $printMinutes = $this->nonNegative($data['print_hours'] ?? 0) * 60 + $this->nonNegative($data['print_minutes'] ?? 0);
        $totalHours = ($printMinutes / 60) * $quantity;
        $energy = (int) round(($this->nonNegative($data['average_watts'] ?? 0) / 1000) * $totalHours * $this->money($data['energy_cost_kwh'] ?? 0));
        $machine = (int) round($totalHours * $this->money($data['machine_rate_hour'] ?? 0));
        $labor = $this->rowsCost($data['labor'] ?? [], $quantity, true);
        $additional = $this->rowsCost($data['additional_costs'] ?? [], $quantity, false);
        $base = $filament['total'] + $energy + $machine + $labor + $additional;
        $riskAdjusted = $risk > 0 ? (int) round($base / (1 - $risk / 100)) : $base;
        $riskCost = $riskAdjusted - $base;

        $pricingMode = ($data['pricing_mode'] ?? 'markup') === 'margin' ? 'margin' : 'markup';
        $pricingPercent = $this->percentage($data['pricing_percentage'] ?? 0, $pricingMode === 'margin' ? 99.99 : 10000, 'Margine o ricarico non valido.');
        $marked = $pricingMode === 'margin'
            ? (int) round($riskAdjusted / (1 - $pricingPercent / 100))
            : (int) round($riskAdjusted * (1 + $pricingPercent / 100));
        $vat = $this->percentage($data['vat_percentage'] ?? 0, 100, 'IVA non valida.');
        $vatIncluded = (bool) ($data['vat_included'] ?? false);
        if ($vatIncluded && $vat > 0) {
            $final = $marked;
            $net = (int) round($final / (1 + $vat / 100));
            $vatCents = $final - $net;
        } else {
            $net = $marked;
            $vatCents = (int) round($net * $vat / 100);
            $final = $net + $vatCents;
        }
        $final = $this->roundCommercial($final, $data['rounding'] ?? 'cent');
        $vatCents = max(0, $final - $net);

        return [
            'quantity' => $quantity, 'filament' => $filament, 'energy_cents' => $energy,
            'machine_cents' => $machine, 'labor_cents' => $labor, 'additional_cents' => $additional,
            'base_cost_cents' => $base, 'risk_cost_cents' => $riskCost, 'total_cost_cents' => $riskAdjusted,
            'cost_per_piece_cents' => (int) round($riskAdjusted / $quantity), 'pricing_mode' => $pricingMode,
            'pricing_percentage' => $pricingPercent, 'profit_cents' => max(0, $net - $riskAdjusted),
            'net_price_cents' => $net, 'vat_cents' => $vatCents, 'final_price_cents' => $final,
            'price_per_piece_cents' => (int) round($final / $quantity), 'print_minutes_total' => (int) round($printMinutes * $quantity),
        ];
    }

    private function rowsCost(array $rows, int $quantity, bool $labor): int
    {
        $total = 0;
        foreach ($rows as $row) {
            $scopeMultiplier = ($row['scope'] ?? 'per_piece') === 'per_piece' ? $quantity : 1;
            $unit = $labor
                ? $this->nonNegative($row['minutes'] ?? 0) / 60 * $this->money($row['hourly_rate'] ?? 0)
                : $this->nonNegative($row['quantity'] ?? 0) * $this->money($row['unit_cost'] ?? 0);
            $total += (int) round($unit * $scopeMultiplier);
        }

        return $total;
    }

    private function money(mixed $value): int
    {
        return (int) round($this->nonNegative($value) * 100);
    }

    private function nonNegative(mixed $value): float
    {
        $number = is_numeric($value) ? (float) $value : 0;
        if ($number < 0) {
            throw new InvalidArgumentException('I valori non possono essere negativi.');
        }

        return $number;
    }

    private function percentage(mixed $value, float $max, string $message): float
    {
        $number = $this->nonNegative($value);
        if ($number > $max) {
            throw new InvalidArgumentException($message);
        }

        return $number;
    }

    private function roundCommercial(int $cents, string $mode): int
    {
        return match ($mode) {
            'none', 'cent' => $cents,
            'half' => (int) round($cents / 50) * 50,
            'euro' => (int) round($cents / 100) * 100,
            '90' => max(90, (int) ceil(($cents - 90) / 100) * 100 + 90),
            '99' => max(99, (int) ceil(($cents - 99) / 100) * 100 + 99),
            default => $cents,
        };
    }
}
