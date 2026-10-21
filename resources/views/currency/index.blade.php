<x-layouts.app title="Cambio valuta">
    <section class="page-head">
        <div><h1>Cambio valuta</h1><p>Convertitore rapido con valore giornaliero aggiornato.</p></div>
    </section>

    <section class="panel translator-shell">
        <form class="translator-form form-stack" method="post" action="{{ route('currency.convert') }}" data-currency-form data-native-submit>@csrf
            <div class="translator-toolbar">
                <label>
                    <span>Da</span>
                    <select name="source">
                        @foreach($currencies as $code => $label)
                            <option value="{{ $code }}" @selected($code === 'EUR')>{{ $code }} - {{ $label }}</option>
                        @endforeach
                    </select>
                </label>
                <button class="icon-btn translator-swap" type="button" data-currency-swap aria-label="Inverti valute" title="Inverti valute">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M7 7h11"/><path d="m14 4 4 3-4 3"/><path d="M17 17H6"/><path d="m10 14-4 3 4 3"/></svg>
                </button>
                <label>
                    <span>A</span>
                    <select name="target">
                        @foreach($currencies as $code => $label)
                            <option value="{{ $code }}" @selected($code === 'USD')>{{ $code }} - {{ $label }}</option>
                        @endforeach
                    </select>
                </label>
            </div>
            <div class="translator-grid currency-grid">
                <label class="translator-panel">
                    <span>Importo</span>
                    <input name="amount" type="number" min="0" step="0.01" value="1" inputmode="decimal">
                </label>
                <section class="translator-panel translator-output">
                    <div class="translator-output-head">
                        <span>Risultato</span>
                        <button class="icon-btn" type="button" data-currency-copy aria-label="Copia risultato" title="Copia risultato">
                            <svg viewBox="0 0 24 24" aria-hidden="true"><rect x="9" y="9" width="11" height="11" rx="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg>
                        </button>
                    </div>
                    <div class="translator-result currency-result" data-currency-result>Il cambio comparirà qui.</div>
                    <p class="translator-note" data-currency-meta>Dati via Frankfurter.</p>
                </section>
            </div>
            <div class="page-actions translator-actions">
                <button class="pill" type="submit">Converti</button>
            </div>
        </form>
    </section>
</x-layouts.app>
