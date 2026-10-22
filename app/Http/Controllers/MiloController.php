<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Http\Response;

class MiloController extends Controller
{
    public function index(): View
    {
        return view('milo.index');
    }

    public function scene(): Response
    {
        return response()->view('milo.scene')->header('Cache-Control', 'no-store, private');
    }
}
