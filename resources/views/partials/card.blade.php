<article class="card">
    <h2>{{ $title }}</h2>
    <div class="card-list">
        @forelse($items as $item)
            @if(is_array($item))
                <a class="card-link" href="{{ $item['url'] }}">{{ $item['label'] }}</a>
            @else
                <p>{{ $item }}</p>
            @endif
        @empty
            <p class="muted">Nessun elemento.</p>
        @endforelse
    </div>
</article>
