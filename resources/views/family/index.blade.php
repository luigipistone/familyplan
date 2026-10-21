<x-layouts.app title="Famiglia">
    <section class="page-head"><h1>Famiglia</h1><p>Impegni correlati ai figli di oggi e domani.</p></section>
    @if(file_exists(public_path('build/manifest.json')))
        <div
            data-vue-family-timeline
            data-events-b64="{{ base64_encode(json_encode($events->map(fn($event) => ['id' => $event->id, 'day_group' => $event->starts_at->isToday() ? 'Oggi' : 'Domani', 'starts_at' => $event->starts_at->format('H:i'), 'child_name' => $event->child->name ?? 'Figlio', 'child_avatar_url' => $event->child?->profilePhotoUrl(), 'child_initials' => $event->child?->avatarInitials() ?? 'F', 'title' => $event->title, 'assigned_name' => $event->assignedUser->name ?? 'Da assegnare'])->values())) }}"
        ></div>
    @else
        @php($todayEvents = $events->filter(fn ($event) => $event->starts_at->isToday()))
        @php($tomorrowEvents = $events->filter(fn ($event) => $event->starts_at->isTomorrow()))
        <div class="stack">
            <section class="stack">
                <h2>Oggi</h2>
                <div class="timeline">@forelse($todayEvents as $event)<article><time>{{ $event->starts_at->format('H:i') }}</time><div class="timeline-body">@if($event->child?->profilePhotoUrl())<img class="avatar avatar-md" src="{{ $event->child->profilePhotoUrl() }}" alt="{{ $event->child->name }}">@else<span class="avatar avatar-md avatar-fallback">{{ $event->child?->avatarInitials() ?? 'F' }}</span>@endif<div class="timeline-copy"><strong>{{ $event->child->name ?? 'Figlio' }}</strong><p>{{ $event->title }} · {{ $event->assignedUser->name ?? 'Da assegnare' }}</p></div></div></article>@empty<p class="muted">Nessun impegno per oggi.</p>@endforelse</div>
            </section>
            <section class="stack">
                <h2>Domani</h2>
                <div class="timeline">@forelse($tomorrowEvents as $event)<article><time>{{ $event->starts_at->format('H:i') }}</time><div class="timeline-body">@if($event->child?->profilePhotoUrl())<img class="avatar avatar-md" src="{{ $event->child->profilePhotoUrl() }}" alt="{{ $event->child->name }}">@else<span class="avatar avatar-md avatar-fallback">{{ $event->child?->avatarInitials() ?? 'F' }}</span>@endif<div class="timeline-copy"><strong>{{ $event->child->name ?? 'Figlio' }}</strong><p>{{ $event->title }} · {{ $event->assignedUser->name ?? 'Da assegnare' }}</p></div></div></article>@empty<p class="muted">Nessun impegno per domani.</p>@endforelse</div>
            </section>
        </div>
    @endif
</x-layouts.app>
