<?= $this->extend('templates/layout') ?>

<?= $this->section('content') ?>
<?php $validation = session('validation') ?? []; ?>
<div class="container">
    <h1>Choix epargne</h1>

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

    <form action="<?= site_url('client/epargne') ?>" method="post">
        <?= csrf_field() ?>

        <div class="form-group">
            <label for="pourcentage">Pourcentage epargne</label>
            <input type="number" name="pourcentage" id="pourcentage" class="form-control" 
            min="0.01" step="0.01" required>
        </div>


        <button type="submit" class="btn btn-primary">Valider le choix</button>
    </form>
</div>

<?= $this->endSection() ?>
