<?php

namespace App\Http\Controllers;

use App\Models\Coin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class CoinController extends Controller
{
    public function index(Request $request)
    {
        $user = $this->authorizedUser($request);
        $coins = Coin::where('owner_id', $user->id)->orderBy('country')->orderByDesc('year')->orderBy('title')->get();

        return view('coins.index', [
            'coins' => $coins,
            'countries' => $coins->pluck('country')->unique()->sort()->values(),
            'stats' => [
                'count' => $coins->count(),
                'face' => $coins->sum(fn ($coin) => (float) $coin->face_value_eur),
                'min' => $coins->sum(fn ($coin) => (float) $coin->estimated_min_eur),
                'max' => $coins->sum(fn ($coin) => (float) $coin->estimated_max_eur),
                'verify' => $coins->where('manual_verification', true)->count(),
            ],
        ]);
    }

    public function store(Request $request)
    {
        $user = $this->authorizedUser($request);
        $coin = Coin::create($this->coinData($request) + ['owner_id' => $user->id]);
        $this->syncImage($coin, $request);

        return back()->with('ok', 'Moneta aggiunta alla collezione.');
    }

    public function update(Request $request, Coin $coin)
    {
        $user = $this->authorizedUser($request);
        abort_unless($coin->owner_id === $user->id, 403);
        $coin->update($this->coinData($request, $coin));
        $this->syncImage($coin, $request);

        return back()->with('ok', 'Moneta aggiornata.');
    }

    public function destroy(Request $request, Coin $coin)
    {
        $user = $this->authorizedUser($request);
        abort_unless($coin->owner_id === $user->id, 403);
        if ($coin->image_path) {
            Storage::disk('public')->delete($coin->image_path);
        }
        $coin->delete();

        return back()->with('ok', 'Moneta eliminata.');
    }

    private function authorizedUser(Request $request)
    {
        $user = $request->user();
        abort_unless($user->can_manage_coin_collection, 403);

        return $user;
    }

    private function coinData(Request $request, ?Coin $coin = null): array
    {
        return $request->validate([
            'code' => ['required', 'string', 'max:30', Rule::unique('coins')->where('owner_id', $request->user()->id)->ignore($coin?->id)],
            'country' => ['required', 'string', 'max:100'],
            'year' => ['nullable', 'integer', 'min:1900', 'max:'.(now()->year + 1)],
            'title' => ['required', 'string', 'max:255'],
            'denomination_eur' => ['nullable', 'numeric', 'min:0', 'max:999999'],
            'type' => ['nullable', 'string', 'max:80'],
            'condition' => ['nullable', 'string', 'max:80'],
            'rarity' => ['nullable', 'string', 'max:80'],
            'estimated_min_eur' => ['nullable', 'numeric', 'min:0', 'max:99999999'],
            'estimated_max_eur' => ['nullable', 'numeric', 'gte:estimated_min_eur', 'max:99999999'],
            'face_value_eur' => ['nullable', 'numeric', 'min:0', 'max:999999'],
            'probable_duplicate' => ['nullable', 'boolean'],
            'identification_confidence' => ['nullable', 'numeric', 'min:0', 'max:1'],
            'manual_verification' => ['nullable', 'boolean'],
            'notes' => ['nullable', 'string', 'max:5000'],
            'coin_image' => ['nullable', 'image', 'max:6144'],
        ]);
    }

    private function syncImage(Coin $coin, Request $request): void
    {
        if (! $request->hasFile('coin_image')) {
            return;
        }
        if ($coin->image_path) {
            Storage::disk('public')->delete($coin->image_path);
        }
        $coin->update(['image_path' => $request->file('coin_image')->store('coin-collection', 'public')]);
    }
}
