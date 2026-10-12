<fieldset class="contact-proof mt-3">
    <legend class="form-label">Contact préalable avec le client (obligatoire)</legend>
    <div class="d-flex gap-4 mb-2">
        <div class="form-check">
            <input class="form-check-input" type="radio" name="mode_contact" id="<?= $prefix ?>-gsm" value="GSM" required>
            <label class="form-check-label" for="<?= $prefix ?>-gsm">Appel GSM</label>
        </div>
        <div class="form-check">
            <input class="form-check-input" type="radio" name="mode_contact" id="<?= $prefix ?>-mail" value="mail">
            <label class="form-check-label" for="<?= $prefix ?>-mail">E-mail</label>
        </div>
    </div>
    <label class="form-label" for="<?= $prefix ?>-motif">Motif</label>
    <input class="form-control" id="<?= $prefix ?>-motif" name="motif" required maxlength="255">
</fieldset>
