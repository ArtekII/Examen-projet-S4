<?= $this->extend('templates/layout') ?>

<?= $this->section('content') ?>
<?php $validation = session('validation') ?? []; ?>
<div class="container">
    <h1>Faire une opération</h1>

    <?php if (session()->has('success')) { ?>
        <div class="alert alert-success">
            <?= esc(session('success')) ?>
        </div>
    <?php } ?>

    <?php if (session()->has('error')) { ?>
        <div class="alert alert-danger">
            <?= esc(session('error')) ?>
        </div>
    <?php } ?>

    <form action="<?= site_url('client/operation') ?>" method="post">
        <?= csrf_field() ?>

        <div class="form-group">
            <label for="id_type_operation">Type d’opération</label>
            <select name="id_type_operation" id="id_type_operation" class="form-control" required>
                <option value="">Choisir une opération</option>
                <?php foreach ($typeOperations as $typeOperation) { ?>
                    <option value="<?= esc($typeOperation['id']) ?>"
                        <?= old('id_type_operation') == $typeOperation['id'] ? 'selected' : '' ?>>
                        <?= esc($typeOperation['libelle']) ?>
                    </option>
                <?php } ?>
            </select>
            <div><?= esc($validation['id_type_operation'] ?? '') ?></div>
        </div>

        <div class="form-group">
            <label for="montant">Montant</label>
            <input type="number" name="montant" id="montant" class="form-control" 
            value="<?= esc(old('montant')) ?>" min="0.01" step="0.01" required>
            <div><?= esc($validation['montant'] ?? '') ?></div>
        </div>

        <div id="include-withdrawal-fees-group" class="form-group">
            <label>
                <input
                    type="checkbox"
                    name="inclure_frais_retrait"
                    value="1"
                >
                Inclure les frais de retrait
            </label>
        </div>

        <div class="form-group" id="beneficiary-group" hidden>
            <label for="numero_beneficiaire">Numéro du bénéficiaire</label>
            <input
                type="text"
                name="numero_beneficiaire"
                id="numero_beneficiaire"
                class="form-control"
                value="<?= esc(old('numero_beneficiaire')) ?>"
                placeholder="Pour un transfert uniquement"
            >
            <div><?= esc($validation['numero_beneficiaire'] ?? '') ?></div>
        </div>

        <button type="submit" class="btn btn-primary">Valider l’opération</button>
    </form>
</div>

<script src="<?= base_url('js/operation.js') ?>"></script>
<?= $this->endSection() ?>
