<?php

namespace App\Http\Controllers;

use App\Services\SoundboardSource;
use Throwable;

class SoundboardController extends Controller
{
    public function __invoke(SoundboardSource $source)
    {
        try {
            $clips = collect($source->clips());
            $sourceAvailable = true;
        } catch (Throwable) {
            $clips = collect();
            $sourceAvailable = false;
        }

        return view('soundboard.index', [
            'clips' => $clips,
            'categories' => $clips->pluck('category')->unique()->values(),
            'sourceAvailable' => $sourceAvailable,
        ]);
    }

    public function image(string $file)
    {
        abort_unless((bool) preg_match('/^[a-z0-9-]+\.webp$/i', $file), 404);
        $path = public_path('media/soundboard/images/'.$file);
        abort_unless(is_file($path), 404);

        return response()->file($path, [
            'Content-Type' => 'image/webp',
            'Cache-Control' => 'private, max-age=2592000, immutable',
        ]);
    }
}
