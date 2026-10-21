<?php

namespace Database\Seeders;

use App\Models\Coin;
use App\Models\User;
use Illuminate\Database\Seeder;

class CoinCatalogSeeder extends Seeder
{
    public function run(): void
    {
        $owner = User::where('role', 'admin')->whereRaw('LOWER(name) = ?', ['luigi'])->first();
        $path = database_path('data/catalogo_monete.json');
        if (! $owner || ! is_file($path)) {
            return;
        }

        $catalog = json_decode(file_get_contents($path), true, flags: JSON_THROW_ON_ERROR);
        foreach ($catalog['items'] ?? [] as $item) {
            Coin::updateOrCreate(
                ['owner_id' => $owner->id, 'code' => $item['codice']],
                [
                    'country' => $item['paese'],
                    'year' => $item['anno'],
                    'title' => $item['titolo'],
                    'denomination_eur' => $item['denominazione_euro'],
                    'type' => $item['tipologia'],
                    'condition' => $item['stato_conservazione'],
                    'rarity' => $item['rarita_indicativa'],
                    'estimated_min_eur' => $item['valore_stimato_min_eur'],
                    'estimated_max_eur' => $item['valore_stimato_max_eur'],
                    'face_value_eur' => $item['valore_facciale_eur'],
                    'probable_duplicate' => $item['duplicato_probabile'],
                    'identification_confidence' => $item['confidenza_identificazione'],
                    'manual_verification' => $item['verifica_manuale_consigliata'],
                    'image_path' => 'coin-collection/'.basename($item['immagine']),
                    'notes' => $item['note'],
                ]
            );
        }
    }
}
