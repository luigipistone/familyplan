<section class="grocery-picker" data-grocery-picker @if($target === 'create') data-grocery-create @endif @if($target === 'draft') data-grocery-draft @endif>
    @foreach($groceryCategories as $category => $items)
        <details class="grocery-accordion" @if($loop->first) open @endif>
            <summary>{{ $category }}</summary>
            <div class="grocery-items is-active">
                @foreach($items as $item)
                    <button type="button" data-grocery-item="{{ $item }}" @if($target === 'store' && $list) data-store-url="{{ route('shopping.items.store', $list) }}" @endif>{{ $item }}</button>
                @endforeach
            </div>
        </details>
    @endforeach
    @if($target === 'create')
        <div class="selected-groceries" data-selected-groceries></div>
        <div data-selected-grocery-inputs></div>
    @endif
    <p class="grocery-feedback" data-grocery-feedback aria-live="polite"></p>
</section>
