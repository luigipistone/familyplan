<x-layouts.app title="Soundboard">
    <section class="page-head">
        <div><h1>Soundboard</h1><p>Le clip di Non Aprite Quella Podcast.</p></div>
    </section>

    @if($sourceAvailable)
        <section class="panel soundboard-tools">
            <label class="contact-search">
                <span>Cerca</span>
                <span class="search-field">
                    <input type="search" placeholder="Titolo della clip" data-soundboard-search autocomplete="off">
                    <button type="button" data-search-clear aria-label="Azzera ricerca" title="Azzera ricerca"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M6 6l12 12M18 6 6 18"/></svg></button>
                </span>
            </label>
            <div class="soundboard-categories" aria-label="Categorie">
                <button class="soundboard-category is-active" type="button" data-soundboard-category="" aria-pressed="true">Tutte</button>
                @foreach($categories as $category)
                    <button class="soundboard-category" type="button" data-soundboard-category="{{ \Illuminate\Support\Str::lower($category) }}" aria-pressed="false">{{ $category }}</button>
                @endforeach
            </div>
        </section>

        <section class="soundboard-grid" data-soundboard-grid>
            @foreach($clips as $clip)
                <button
                    class="soundboard-clip"
                    type="button"
                    data-soundboard-clip
                    data-soundboard-name="{{ \Illuminate\Support\Str::lower($clip['name']) }}"
                    data-soundboard-category-name="{{ \Illuminate\Support\Str::lower($clip['category']) }}"
                    data-soundboard-audio="{{ $clip['audio_url'] }}"
                    aria-label="Riproduci {{ $clip['name'] }}"
                >
                    <span class="soundboard-media">
                        @if($clip['image_url'])
                            <img src="{{ $clip['image_url'] }}" alt="" loading="lazy" decoding="async" fetchpriority="low">
                        @else
                            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M9 18V5l11-2v13"/><circle cx="6" cy="18" r="3"/><circle cx="17" cy="16" r="3"/></svg>
                        @endif
                        <span class="soundboard-play" aria-hidden="true">
                            <svg class="soundboard-play-icon" viewBox="0 0 24 24"><path d="m9 7 8 5-8 5Z"/></svg>
                            <svg class="soundboard-stop-icon" viewBox="0 0 24 24"><rect x="8" y="8" width="8" height="8" rx="1"/></svg>
                        </span>
                    </span>
                    <span class="soundboard-clip-name">{{ $clip['name'] }}</span>
                    <span class="soundboard-clip-category">{{ $clip['category'] }}</span>
                </button>
            @endforeach
        </section>

        <section class="card soundboard-empty" data-soundboard-empty @if($clips->isNotEmpty()) hidden @endif>
            <p>Nessuna clip trovata.</p>
        </section>
    @else
        <section class="card soundboard-empty"><p>La soundboard non è disponibile in questo momento. Riprova più tardi.</p></section>
    @endif
</x-layouts.app>
