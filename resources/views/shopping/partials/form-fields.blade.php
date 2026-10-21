<div class="two">
    <label>Nome lista<input name="name" value="{{ $list->name ?? '' }}" data-shopping-field="name" required></label>
    <label>Data<input type="date" name="list_date" value="{{ isset($list) && $list ? $list->list_date->format('Y-m-d') : now()->format('Y-m-d') }}" data-shopping-field="list_date" required></label>
</div>
<fieldset class="check-list">
    <legend>Condividi</legend>
    @foreach($users as $u)
        <label class="check"><input type="checkbox" name="shared_with[]" value="{{ $u->id }}" @checked(in_array($u->id, $list->shared_with ?? []))> {{ $u->name }}</label>
    @endforeach
</fieldset>
