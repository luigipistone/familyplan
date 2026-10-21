<x-layouts.app title="Film e serie">
    <section class="page-head movies-head">
        <div><h1>Film e serie</h1><p>Scopri, salva e condividi cosa vedere.</p></div>
    </section>

    @if(!$configured)
        <section class="card movies-message"><p>Per mostrare i titoli deve essere configurata la chiave gratuita TMDB.</p></section>
    @else
        <section class="panel movies-search-panel">
            <form data-movie-search data-native-submit data-search-url="{{ route('movies.search') }}">
                <label class="contact-search"><span>Cerca</span><span class="search-field">
                    <input type="search" name="query" placeholder="Film o serie TV" autocomplete="off" minlength="2">
                    <button type="button" data-search-clear aria-label="Azzera ricerca" title="Azzera ricerca"><svg viewBox="0 0 24 24"><path d="M6 6l12 12M18 6 6 18"/></svg></button>
                </span></label>
                <label class="movie-type-filter"><span>Tipo</span><select name="type"><option value="multi">Tutti</option><option value="movie">Film</option><option value="tv">Serie TV</option></select></label>
                <button class="primary-btn" type="submit">Cerca</button>
            </form>
        </section>

        @if($error)<div class="alert">{{ $error }}</div>@endif

        <section class="movies-section" data-movie-search-section hidden>
            <div class="section-title"><h2>Risultati</h2></div>
            <div class="movie-coverflow" data-movie-coverflow data-detail-url="{{ url('/film') }}"></div>
            <p class="movies-empty" data-movie-empty hidden>Nessun titolo trovato.</p>
        </section>

        <section class="movies-section movie-library" data-movie-library>
            <div class="section-title"><h2>La mia raccolta</h2><span>{{ $mine->count() }} titoli</span></div>
            <div class="movie-library-filters" aria-label="Filtra la raccolta">
                <button class="is-active" type="button" data-library-filter="all" aria-pressed="true">Tutti</button>
                <button type="button" data-library-filter="watchlist" aria-pressed="false">Da vedere</button>
                <button type="button" data-library-filter="watched" aria-pressed="false">Visti</button>
                <button type="button" data-library-filter="favorite" aria-pressed="false">Preferiti</button>
            </div>
            @if($mine->isNotEmpty())
                @include('movies._coverflow', ['movies' => $mine])
            @else
                <p class="movies-empty">Non hai ancora salvato film o serie. Apri una locandina e scegli “Salva”.</p>
            @endif
            <p class="movies-empty" data-library-empty hidden>Nessun titolo in questa categoria.</p>
        </section>
        @if($shared->isNotEmpty())
            <section class="movies-section"><div class="section-title"><h2>Condivisi in famiglia</h2></div>
                @include('movies._coverflow', ['movies' => $shared])
            </section>
        @endif

        @foreach([['title' => 'Film suggeriti per te', 'movies' => $sections['suggestedMovies']]] as $section)
            @if(count($section['movies']))
                <section class="movies-section"><div class="section-title"><h2>{{ $section['title'] }}</h2></div>
                    @include('movies._coverflow', ['movies' => $section['movies']])
                </section>
            @endif
        @endforeach

        <section class="movies-section movie-genre-section" data-movie-genre-section data-genre-url="{{ route('movies.genre') }}">
            <div class="section-title movie-genre-title"><h2>Film per genere</h2>
                <select data-movie-genre aria-label="Scegli il genere"><option value="">Scegli genere</option>@foreach($genres as $genre)<option value="{{ $genre['id'] }}">{{ $genre['name'] }}</option>@endforeach</select>
            </div>
            <div class="movie-coverflow" data-movie-coverflow data-detail-url="{{ url('/film') }}"><p class="movies-empty">Seleziona un genere per ricevere nuovi suggerimenti.</p></div>
        </section>

        @foreach([
            ['title' => 'Serie consigliate del momento', 'movies' => $sections['suggestedSeries']],
            ['title' => 'Ora al cinema', 'movies' => $sections['nowPlaying']],
        ] as $section)
            @if(count($section['movies']))
                <section class="movies-section"><div class="section-title"><h2>{{ $section['title'] }}</h2></div>
                    @include('movies._coverflow', ['movies' => $section['movies']])
                </section>
            @endif
        @endforeach

        <dialog class="modal movie-modal" data-movie-modal data-save-template="{{ url('/film/__ID__/lista') }}" data-delete-template="{{ url('/film/lista/__ID__') }}">
            <div class="modal-card movie-modal-card">
                <div class="modal-head"><div><p data-movie-kind>Titolo</p><h2 data-movie-title>Dettagli</h2></div>
                    <button class="icon-btn" type="button" data-movie-close aria-label="Chiudi" title="Chiudi"><svg viewBox="0 0 24 24"><path d="m6 6 12 12M18 6 6 18"/></svg></button>
                </div>
                <div data-movie-detail class="movie-detail"></div>
            </div>
        </dialog>
    @endif

    <p class="movies-credit">Dati e immagini forniti da TMDB. Questo prodotto utilizza l'API TMDB ma non è approvato o certificato da TMDB.</p>
</x-layouts.app>
