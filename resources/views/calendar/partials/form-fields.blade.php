<div class="two">
    <label>Titolo<input name="title" data-event-field="title" required></label>
    <label>Inizio<input type="datetime-local" name="starts_at" data-event-field="starts_at" required></label>
</div>
<div class="two">
    <label>Fine<input type="datetime-local" name="ends_at" data-event-field="ends_at"></label>
    <label>Visibilità<select name="visibility" data-event-field="visibility"><option value="family">Famiglia</option><option value="private">Privato</option></select></label>
</div>
<div class="two">
    <label>Assegnato a<select name="assigned_user_id" data-event-field="assigned_user_id"><option value="">Nessuno</option>@foreach($users as $u)<option value="{{ $u->id }}">{{ $u->name }}</option>@endforeach</select></label>
    <label>Figlio<select name="child_id" data-event-field="child_id"><option value="">Nessuno</option>@foreach($children as $u)<option value="{{ $u->id }}">{{ $u->name }}</option>@endforeach</select></label>
</div>
<label>Dettagli<textarea name="description" rows="4" data-event-field="description"></textarea></label>
