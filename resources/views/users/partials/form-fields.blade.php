<div class="two">
    <label>Nome<input name="name" data-user-field="name" required></label>
    <label data-phone-field>Telefono<input name="phone" data-user-field="phone"></label>
</div>
<div class="two">
    <label data-email-field>Email<input type="email" name="email" data-user-field="email"></label>
    <label data-password-field>{{ !empty($edit) ? 'Nuova password' : 'Password' }}
        <span class="input-with-icon password-field">
            <input type="password" name="password" minlength="12" autocomplete="new-password" data-user-field="password" data-password-input placeholder="{{ !empty($edit) ? 'Lascia vuoto per non cambiarla' : '' }}">
            <span class="password-toggle" role="button" tabindex="0" data-password-toggle aria-label="Mostra password" title="Mostra password">
                <svg class="eye-open" viewBox="0 0 24 24" aria-hidden="true"><path d="M2.5 12s3.5-6 9.5-6 9.5 6 9.5 6-3.5 6-9.5 6-9.5-6-9.5-6Z"/><circle cx="12" cy="12" r="3"/></svg>
                <svg class="eye-closed" viewBox="0 0 24 24" aria-hidden="true"><path d="M3 3l18 18"/><path d="M10.6 10.6A2.9 2.9 0 0 0 12 15a3 3 0 0 0 2.7-1.7"/><path d="M7.4 7.4C4.4 9 2.5 12 2.5 12s3.5 6 9.5 6c1.7 0 3.1-.4 4.4-1"/><path d="M12.6 6.1C18.2 6.5 21.5 12 21.5 12a14.7 14.7 0 0 1-2.1 2.7"/></svg>
            </span>
        </span>
    </label>
</div>
<div class="two">
    <label>Ruolo<select name="role" data-user-field="role"><option value="familiare">Familiare</option><option value="admin">Admin</option></select></label>
    <label>Tipologia<select name="category" data-user-field="category" data-user-category><option value="mamma">Mamma</option><option value="papa">Papà</option><option value="figlio">Figlio</option><option value="nonno">Nonno</option><option value="zia">Zia</option><option value="altro">Altro</option></select></label>
</div>
<div class="two" data-child-parents hidden>
    <label>Mamma<select name="mother_id" data-user-field="mother_id"><option value="">Nessuna</option>@foreach($mothers as $p)<option value="{{ $p->id }}">{{ $p->name }}</option>@endforeach</select></label>
    <label>Papà<select name="father_id" data-user-field="father_id"><option value="">Nessuno</option>@foreach($fathers as $p)<option value="{{ $p->id }}">{{ $p->name }}</option>@endforeach</select></label>
</div>
<label>Data di nascita<input type="date" name="birth_date" data-user-field="birth_date" data-user-birth-date></label>
<div class="avatar-upload-panel">
    <span class="avatar avatar-xl avatar-fallback">FP</span>
    <label>Immagine profilo<input type="file" name="profile_photo" accept="image/*"></label>
</div>
<label>Info
    <textarea name="personal_info" rows="4" data-user-field="personal_info" data-wysiwyg-input hidden></textarea>
    <div class="wysiwyg" data-wysiwyg>
        <div class="wysiwyg-toolbar" aria-label="Formattazione testo">
            <button type="button" data-wysiwyg-command="bold" title="Grassetto"><strong>B</strong></button>
            <button type="button" data-wysiwyg-command="italic" title="Corsivo"><em>I</em></button>
            <button type="button" data-wysiwyg-command="underline" title="Sottolineato"><span style="text-decoration: underline;">U</span></button>
            <button type="button" data-wysiwyg-command="strikeThrough" title="Barrato"><span style="text-decoration: line-through;">S</span></button>
            <button type="button" data-wysiwyg-command="insertUnorderedList" title="Lista">•</button>
            <button type="button" data-wysiwyg-command="insertOrderedList" title="Lista numerata">1.</button>
            <button type="button" data-wysiwyg-command="formatBlock" data-wysiwyg-value="blockquote" title="Citazione">"</button>
            <button type="button" data-wysiwyg-command="createLink" title="Link">Link</button>
            <button type="button" data-wysiwyg-command="removeFormat" title="Rimuovi formato">Tx</button>
        </div>
        <div class="wysiwyg-editor" contenteditable="true" data-wysiwyg-editor data-placeholder="Aggiungi note, dettagli sanitari, abitudini o informazioni utili."></div>
    </div>
</label>
@if(!empty($edit))
    <label class="check"><input type="checkbox" name="is_active" value="1" data-user-field="is_active"> Account attivo</label>
@endif
