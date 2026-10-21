<x-layouts.app title="Figli">
    <section class="page-head"><h1>Figli</h1><p>Gestione profili collegati al genitore. Sotto i 14 anni gestione completa, sopra i 14 solo dati organizzativi familiari.</p></section>
    @if(file_exists(public_path('build/manifest.json')))
        <div
            data-vue-children-cards
            data-children-b64="{{ base64_encode(json_encode($children->map(function ($child) { $age = $child->birth_date ? $child->birth_date->age : null; return ['id' => $child->id, 'name' => $child->name, 'age_label' => $age !== null ? $age.' anni' : 'Eta non impostata', 'management_label' => ($age !== null && $age < 14) ? 'gestione completa genitore' : 'gestione limitata', 'personal_info' => $child->personal_info, 'avatar_url' => $child->profilePhotoUrl(), 'initials' => $child->avatarInitials()]; })->values())) }}"
        ></div>
    @else
        <div class="stack">
            @forelse($children as $child)
                @php($age = $child->birth_date ? $child->birth_date->age : null)
                <article class="card">
                    <div class="child-card-head">
                        @if($child->profilePhotoUrl())
                            <img class="avatar avatar-lg" src="{{ $child->profilePhotoUrl() }}" alt="{{ $child->name }}">
                        @else
                            <span class="avatar avatar-lg avatar-fallback">{{ $child->avatarInitials() }}</span>
                        @endif
                        <div>
                            <h2>{{ $child->name }}</h2>
                            <p>{{ $age !== null ? $age.' anni' : 'Età non impostata' }} · {{ ($age !== null && $age < 14) ? 'gestione completa genitore' : 'gestione limitata' }}</p>
                        </div>
                    </div>
                    <small>{{ $child->personal_info }}</small>
                </article>
            @empty
                <p class="muted">Nessun profilo figlio collegato.</p>
            @endforelse
        </div>
    @endif
</x-layouts.app>
