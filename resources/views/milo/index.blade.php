<x-layouts.app title="L’isola di Milo · FamilyPlan">
    <section class="milo-page" aria-label="L’isola di Milo, gioco educativo 3D">
        @php($testMode = request()->boolean('prova'))
        <div class="milo-mode-bar">
            <span>{{ $testMode ? 'Prova adulto · nessun salvataggio' : 'Vuoi esplorare il gioco?' }}</span>
            <a class="milo-mode-link" href="{{ $testMode ? route('milo') : route('milo', ['prova' => 1]) }}">{{ $testMode ? 'Esci dalla prova' : 'Prova adulto' }}</a>
        </div>
        <iframe class="milo-frame" src="{{ route('milo.scene', $testMode ? ['prova' => 1] : []) }}" title="{{ $testMode ? 'Prova adulto senza salvataggi' : 'L’isola di Milo: dodici avventure 3D sulle buone domande' }}" allow="fullscreen" allowfullscreen></iframe>
        <p class="milo-page-note">{{ $testMode ? 'Puoi provare tutti i capitoli. Uscendo o ricaricando, la prova riparte da zero e i progressi normali restano invariati.' : 'Un’avventura alla volta, insieme a un adulto. I progressi rimangono su questo dispositivo.' }}</p>
    </section>
</x-layouts.app>
