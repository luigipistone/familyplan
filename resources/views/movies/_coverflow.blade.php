<div class="movie-coverflow" data-movie-coverflow data-detail-url="{{ url('/film') }}">
    @foreach($movies as $movie)
        <button class="movie-cover" type="button" data-movie-id="{{ $movie['id'] }}" data-media-type="{{ $movie['media_type'] ?? 'movie' }}" data-entry-status="{{ $movie['status'] ?? '' }}" data-entry-favorite="{{ ($movie['is_favorite'] ?? false) ? '1' : '0' }}" aria-label="Apri {{ $movie['title'] }}">
            <span class="movie-poster">
                @if($movie['poster'] ?? null)<img src="{{ $movie['poster'] }}" alt="Locandina di {{ $movie['title'] }}" loading="lazy" decoding="async">@else<span class="movie-poster-empty">{{ mb_substr($movie['title'], 0, 1) }}</span>@endif
                @if($movie['is_favorite'] ?? false)<span class="movie-favorite-badge" aria-label="Preferito">★</span>@endif
            </span>
            <strong>{{ $movie['title'] }}</strong>
            <span>{{ $movie['year'] ?: 'Anno n.d.' }} @if($movie['rating'] ?? null) · {{ number_format($movie['rating'], 1, ',', '') }}@endif</span>
            @if($movie['owner'] ?? null)<small>Condiviso da {{ $movie['owner'] }}</small>@endif
        </button>
    @endforeach
</div>
