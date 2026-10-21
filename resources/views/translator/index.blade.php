<x-layouts.app title="Traduttore">
    <section class="page-head">
        <div><h1>Traduttore</h1><p>Traduzione rapida testo con interfaccia mobile-first e risposta senza ricaricare la pagina.</p></div>
    </section>

    <section class="panel translator-shell">
        <form class="translator-form form-stack" method="post" action="{{ route('translator.translate') }}" data-translator-form data-native-submit>@csrf
            <div class="translator-toolbar">
                <label>
                    <span>Da</span>
                    <select name="source">
                        @foreach($languages as $code => $label)
                            <option value="{{ $code }}" @selected($code === 'it')>{{ $label }}</option>
                        @endforeach
                    </select>
                </label>
                <button class="icon-btn translator-swap" type="button" data-translator-swap aria-label="Inverti lingue" title="Inverti lingue">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M7 7h11"/><path d="m14 4 4 3-4 3"/><path d="M17 17H6"/><path d="m10 14-4 3 4 3"/></svg>
                </button>
                <label>
                    <span>A</span>
                    <select name="target">
                        @foreach($languages as $code => $label)
                            <option value="{{ $code }}" @selected($code === 'en')>{{ $label }}</option>
                        @endforeach
                    </select>
                </label>
            </div>
            <div class="translator-grid">
                <label class="translator-panel">
                    <span>Testo da tradurre</span>
                    <textarea name="text" rows="8" maxlength="3000" placeholder="Scrivi o incolla qui."></textarea>
                </label>
                <section class="translator-panel translator-output">
                    <div class="translator-output-head">
                        <span>Risultato</span>
                        <button class="icon-btn" type="button" data-translator-copy aria-label="Copia traduzione" title="Copia traduzione">
                            <svg viewBox="0 0 24 24" aria-hidden="true"><rect x="9" y="9" width="11" height="11" rx="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg>
                        </button>
                    </div>
                    <div class="translator-result" data-translator-result>La traduzione comparirà qui.</div>
                    <p class="translator-note" data-translator-meta>Traduzione via MyMemory.</p>
                </section>
            </div>
            <div class="page-actions translator-actions">
                <button class="pill" type="submit">Traduci</button>
            </div>
        </form>
    </section>
</x-layouts.app>
