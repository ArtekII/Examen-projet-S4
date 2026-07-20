<?= $this->extend('templates/layout') ?>

<?= $this->section('content') ?>
<div class="container">
    <h1>Solde</h1>
    <?php if (session()->has('success')) { ?>
        <div class="alert alert-success">
            <?= session('success') ?>
        </div>
    <?php } ?>
    <?php if (session()->has('error')) { ?>
        <div class="alert alert-danger">
            <?= session('error') ?>
        </div>
    <?php } ?>
    <p>Nom du client : <?= esc(session()->get('nom_client')) ?></p>
    <p>
        Opérateur :
        <?= esc($operateur['nom_operateur'] ?? 'Non reconnu') ?>
        <?php if (! empty($operateur['prefixe'])) { ?>
            (<?= esc($operateur['prefixe']) ?>)
        <?php } ?>
    </p>
    <p>Solde actuel : <?= esc(number_format($solde, 2, ',', ' ')) ?> Ar</p>
</div>
<?= $this->endSection() ?>
