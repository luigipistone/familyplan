@php($coin = $coin ?? null)
<div class="two coin-form-grid">
    <label>Codice<input name="code" value="{{ old('code', $coin?->code) }}" required placeholder="MON-060"></label>
    <label>Paese<input name="country" value="{{ old('country', $coin?->country) }}" required></label>
    <label>Anno<input type="number" name="year" min="1900" max="{{ now()->year + 1 }}" value="{{ old('year', $coin?->year) }}"></label>
    <label>Valore nominale (€)<input type="number" name="denomination_eur" min="0" step="0.01" value="{{ old('denomination_eur', $coin?->denomination_eur) }}"></label>
</div>
<label>Titolo<input name="title" value="{{ old('title', $coin?->title) }}" required></label>
<div class="two coin-form-grid">
    <label>Tipologia<input name="type" value="{{ old('type', $coin?->type) }}" placeholder="Commemorativa"></label>
    <label>Conservazione<input name="condition" value="{{ old('condition', $coin?->condition) }}" placeholder="Circolata"></label>
    <label>Rarità indicativa<input name="rarity" value="{{ old('rarity', $coin?->rarity) }}" placeholder="Comune"></label>
    <label>Valore facciale (€)<input type="number" name="face_value_eur" min="0" step="0.01" value="{{ old('face_value_eur', $coin?->face_value_eur) }}"></label>
    <label>Stima minima (€)<input type="number" name="estimated_min_eur" min="0" step="0.01" value="{{ old('estimated_min_eur', $coin?->estimated_min_eur) }}"></label>
    <label>Stima massima (€)<input type="number" name="estimated_max_eur" min="0" step="0.01" value="{{ old('estimated_max_eur', $coin?->estimated_max_eur) }}"></label>
    <label>Confidenza identificazione<input type="number" name="identification_confidence" min="0" max="1" step="0.01" value="{{ old('identification_confidence', $coin?->identification_confidence) }}"></label>
    <label>Fotografia<input type="file" name="coin_image" accept="image/*"></label>
</div>
<div class="coin-checks">
    <label class="check"><input type="hidden" name="probable_duplicate" value="0"><input type="checkbox" name="probable_duplicate" value="1" @checked(old('probable_duplicate', $coin?->probable_duplicate))> Possibile duplicato</label>
    <label class="check"><input type="hidden" name="manual_verification" value="0"><input type="checkbox" name="manual_verification" value="1" @checked(old('manual_verification', $coin?->manual_verification))> Identificazione da verificare</label>
</div>
<label>Note<textarea name="notes" rows="3">{{ old('notes', $coin?->notes) }}</textarea></label>
