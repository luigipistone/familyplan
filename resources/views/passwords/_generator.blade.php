<button class="text-action password-generator-toggle" type="button" data-password-generator-toggle>Genera Password</button>
<div class="password-generator" data-password-generator hidden>
    <div class="password-generator-row">
        <label>Lunghezza<input type="number" min="8" max="64" value="18" data-password-length></label>
        <button type="button" data-password-generate>Genera</button>
    </div>
    <div class="password-generator-options">
        <label><input type="checkbox" checked data-password-letters><span>Lettere</span></label>
        <label><input type="checkbox" checked data-password-numbers><span>Numeri</span></label>
        <label><input type="checkbox" checked data-password-symbols><span>Simboli</span></label>
    </div>
</div>
