<?= $this->extend('templates/layout') ?>

<?= $this->section('content') ?>
<div class="container">
    <h1>Connexion</h1>
    <?php if (session()->has('error')){ ?>
        <div class="alert alert-danger">
            <?= session('error') ?>
        </div>
    <?php } ?>
    <form action="<?= site_url('client/login') ?>" method="post">
        <?= csrf_field() ?>

        <div class="form-group">
            <label for="numero">Numéro de téléphone</label>
            <input type="text" name="numero" id="numero" class="form-control" required>
        </div>

        <button type="submit" class="btn btn-primary">Se connecter</button>
    </form>
</div>
<?= $this->endSection() ?>