<?= $this->extend('templates/layout_admin') ?>

<?= $this->section('content') ?>
<div class="admin-page">
    <div class="admin-page__header">
        <h1>Soldes des clients</h1>
        <p>Consultez le solde actuel de chaque client.</p>
    </div>

    <div class="table-wrapper">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nom du client</th>
                    <th>Solde</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($soldes as $solde): ?>
                    <tr>
                        <td><?= esc($solde['id']) ?></td>
                        <td><?= esc($solde['nom_client']) ?></td>
                        <td><?= esc($solde['SOLDE']) ?> Ar</td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <a href="<?= site_url('operateur/soldes/export-csv') ?>" target="_blank" class="btn">
        Exporter en CSV
    </a>
</div>
<?= $this->endSection() ?>