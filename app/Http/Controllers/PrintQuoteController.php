<?php

namespace App\Http\Controllers;

use App\Models\PrintQuote;
use App\Services\MakerWorldImporter;
use App\Services\PrintCostCalculator;
use App\Services\ThreeMfImporter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class PrintQuoteController extends Controller
{
    public function index(Request $request)
    {
        return view('print-quotes.index');
    }

    public function import(Request $request, MakerWorldImporter $importer)
    {
        $data = $request->validate(['url' => ['required', 'url:https', 'max:500']]);

        return response()->json($importer->import($data['url']));
    }

    public function importThreeMf(Request $request, ThreeMfImporter $importer)
    {
        $request->validate(['model_file' => ['required', 'file', 'max:51200', 'extensions:3mf']]);
        try {
            return response()->json($importer->import($request->file('model_file')->getRealPath()));
        } catch (\InvalidArgumentException $exception) {
            throw ValidationException::withMessages(['model_file' => $exception->getMessage()]);
        }
    }

    public function store(Request $request, PrintCostCalculator $calculator)
    {
        [$general, $model, $calculation] = $this->validatedData($request);
        $results = $this->calculate($calculator, $calculation);
        $quote = PrintQuote::create($general + [
            'owner_id' => $request->user()->id,
            'quote_number' => '3D-'.now()->format('Ymd').'-'.strtoupper(Str::random(6)),
            'model_data' => $model, 'calculation_data' => $calculation, 'results' => $results,
            'total_cost_cents' => $results['total_cost_cents'], 'final_price_cents' => $results['final_price_cents'],
        ]);
        $this->syncFiles($request, $quote);

        return response()->json(['message' => 'Preventivo salvato.', 'quote' => $this->resource($quote->fresh())], 201);
    }

    public function update(Request $request, PrintQuote $quote, PrintCostCalculator $calculator)
    {
        $this->authorizeOwner($request, $quote);
        [$general, $model, $calculation] = $this->validatedData($request);
        $results = $this->calculate($calculator, $calculation);
        $quote->update($general + ['model_data' => $model, 'calculation_data' => $calculation, 'results' => $results, 'total_cost_cents' => $results['total_cost_cents'], 'final_price_cents' => $results['final_price_cents']]);
        $this->syncFiles($request, $quote);

        return response()->json(['message' => 'Preventivo aggiornato.', 'quote' => $this->resource($quote->fresh())]);
    }

    public function duplicate(Request $request, PrintQuote $quote)
    {
        $this->authorizeOwner($request, $quote);
        $copy = $quote->replicate(['quote_number', 'image_path', 'source_file_path']);
        $copy->quote_number = '3D-'.now()->format('Ymd').'-'.strtoupper(Str::random(6));
        $copy->name = Str::limit($quote->name.' copia', 150, '');
        $copy->status = 'draft';
        $copy->save();

        return response()->json(['message' => 'Preventivo duplicato.', 'quote' => $this->resource($copy)], 201);
    }

    public function destroy(Request $request, PrintQuote $quote)
    {
        $this->authorizeOwner($request, $quote);
        Storage::disk('public')->delete(array_filter([$quote->image_path, $quote->source_file_path]));
        $quote->delete();

        return response()->json(['message' => 'Preventivo eliminato.']);
    }

    public function printable(Request $request, PrintQuote $quote)
    {
        $this->authorizeOwner($request, $quote);

        return view('print-quotes.print', ['quote' => $quote, 'detailed' => $request->boolean('detailed')]);
    }

    private function validatedData(Request $request): array
    {
        $payload = json_decode((string) $request->input('payload'), true);
        if (! is_array($payload)) {
            throw ValidationException::withMessages(['payload' => 'Dati del preventivo non validi.']);
        }
        $data = Validator::make($payload, [
            'name' => ['required', 'string', 'max:150'], 'model_name' => ['nullable', 'string', 'max:200'],
            'customer' => ['nullable', 'string', 'max:150'], 'status' => ['required', 'in:draft,ready,sent,accepted,rejected,archived'],
            'currency' => ['required', 'in:EUR'], 'source_url' => ['nullable', 'url:https', 'max:500'], 'external_id' => ['nullable', 'string', 'max:100'],
            'valid_until' => ['nullable', 'date'], 'notes' => ['nullable', 'string', 'max:5000'],
            'model' => ['nullable', 'array'], 'model.author' => ['nullable', 'string', 'max:200'], 'model.licenseName' => ['nullable', 'string', 'max:200'],
            'model.thumbnailUrl' => ['nullable', 'url:https', 'max:1000'], 'model.commercialUseStatus' => ['nullable', 'string', 'max:200'],
            'model.printProfile' => ['nullable', 'string', 'max:200'], 'model.printerProfile' => ['nullable', 'string', 'max:200'],
            'model.material' => ['nullable', 'string', 'max:100'], 'model.layerHeight' => ['nullable', 'numeric', 'min:0', 'max:10'],
            'model.plates' => ['nullable', 'integer', 'min:1', 'max:1000'], 'model.colors' => ['nullable', 'integer', 'min:1', 'max:100'], 'model.supports' => ['boolean'],
            'calculation' => ['required', 'array'], 'calculation.quantity' => ['required', 'integer', 'min:1', 'max:10000'],
            'calculation.failure_percentage' => ['numeric', 'min:0', 'max:90'], 'calculation.print_hours' => ['numeric', 'min:0', 'max:10000'],
            'calculation.print_minutes' => ['numeric', 'min:0', 'max:59'], 'calculation.average_watts' => ['numeric', 'min:0', 'max:10000'],
            'calculation.energy_cost_kwh' => ['numeric', 'min:0', 'max:100'], 'calculation.machine_rate_hour' => ['numeric', 'min:0', 'max:10000'],
            'calculation.pricing_mode' => ['required', 'in:markup,margin'], 'calculation.pricing_percentage' => ['numeric', 'min:0', 'max:10000'],
            'calculation.vat_percentage' => ['numeric', 'min:0', 'max:100'], 'calculation.vat_included' => ['boolean'],
            'calculation.rounding' => ['required', 'in:none,cent,half,euro,90,99'],
            'calculation.filaments' => ['array', 'max:20'], 'calculation.filaments.*' => ['array'],
            'calculation.filaments.*.name' => ['nullable', 'string', 'max:120'], 'calculation.filaments.*.brand' => ['nullable', 'string', 'max:120'],
            'calculation.filaments.*.material' => ['nullable', 'string', 'max:60'], 'calculation.filaments.*.color' => ['nullable', 'string', 'max:60'],
            'calculation.filaments.*.spool_cost' => ['numeric', 'min:0', 'max:100000'], 'calculation.filaments.*.spool_grams' => ['numeric', 'min:0', 'max:100000'],
            'calculation.filaments.*.model_grams' => ['numeric', 'min:0', 'max:1000000'], 'calculation.filaments.*.support_grams' => ['numeric', 'min:0', 'max:1000000'],
            'calculation.filaments.*.purge_grams' => ['numeric', 'min:0', 'max:1000000'], 'calculation.filaments.*.waste_percentage' => ['numeric', 'min:0', 'max:1000'],
            'calculation.labor' => ['array', 'max:30'], 'calculation.labor.*' => ['array'],
            'calculation.labor.*.description' => ['nullable', 'string', 'max:200'], 'calculation.labor.*.minutes' => ['numeric', 'min:0', 'max:100000'],
            'calculation.labor.*.hourly_rate' => ['numeric', 'min:0', 'max:100000'], 'calculation.labor.*.scope' => ['required', 'in:fixed_order,per_piece,per_batch'],
            'calculation.additional_costs' => ['array', 'max:30'], 'calculation.additional_costs.*' => ['array'],
            'calculation.additional_costs.*.description' => ['nullable', 'string', 'max:200'], 'calculation.additional_costs.*.quantity' => ['numeric', 'min:0', 'max:100000'],
            'calculation.additional_costs.*.unit_cost' => ['numeric', 'min:0', 'max:100000'], 'calculation.additional_costs.*.scope' => ['required', 'in:fixed_order,per_piece,per_batch'],
        ])->validate();
        if (! empty($data['source_url']) && ! in_array(strtolower(parse_url($data['source_url'], PHP_URL_HOST) ?: ''), ['makerworld.com', 'www.makerworld.com'], true)) {
            throw ValidationException::withMessages(['source_url' => 'Il link deve appartenere a MakerWorld.']);
        }
        if (($data['calculation']['pricing_mode'] ?? '') === 'margin' && ($data['calculation']['pricing_percentage'] ?? 0) >= 100) {
            throw ValidationException::withMessages(['pricing_percentage' => 'Il margine deve essere inferiore al 100%.']);
        }
        $general = collect($data)->only(['name', 'model_name', 'customer', 'status', 'currency', 'source_url', 'external_id', 'valid_until', 'notes'])->all();

        return [$general, $data['model'] ?? [], $data['calculation']];
    }

    private function calculate(PrintCostCalculator $calculator, array $data): array
    {
        try {
            return $calculator->calculate($data);
        } catch (\InvalidArgumentException $e) {
            throw ValidationException::withMessages(['calculation' => $e->getMessage()]);
        }
    }

    private function syncFiles(Request $request, PrintQuote $quote): void
    {
        $request->validate(['image' => ['nullable', 'image', 'max:5120'], 'model_file' => ['nullable', 'file', 'max:51200', 'extensions:3mf,gcode']]);
        foreach (['image' => ['image_path', 'print-quotes/images'], 'model_file' => ['source_file_path', 'print-quotes/source-files']] as $field => [$column, $directory]) {
            if ($request->hasFile($field)) {
                Storage::disk('public')->delete($quote->{$column});
                $quote->update([$column => $request->file($field)->store($directory, 'public')]);
            }
        }
    }

    private function authorizeOwner(Request $request, PrintQuote $quote): void
    {
        abort_unless($quote->owner_id === $request->user()->id, 403);
    }

    private function resource(PrintQuote $quote): array
    {
        return array_merge($quote->toArray(), ['image_url' => $quote->imageUrl(), 'update_url' => route('print-quotes.update', $quote), 'delete_url' => route('print-quotes.destroy', $quote), 'duplicate_url' => route('print-quotes.duplicate', $quote), 'print_url' => route('print-quotes.print', $quote)]);
    }
}
