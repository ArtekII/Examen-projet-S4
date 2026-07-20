<?= $this->extend('templates/layout_admin') ?>

<?= $this->section('styles') ?>
<link rel="stylesheet" href="<?= base_url('css/commissions.css') ?>">
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="admin-page commissions-page">
    <div class="admin-page__header">
        <h1>Commissions perçues par opérateur</h1>
        <p>Consultez les commissions générées par les transferts vers les autres opérateurs.</p>
    </div>

    <div class="commission-summary">
        <span class="commission-summary__label">Total des commissions perçues</span>
        <strong class="commission-summary__value">
            <?= esc(number_format((float) $totalCommissions, 2, ',', ' ')) ?> Ar
        </strong>
    </div>

    <div class="table-wrapper">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Opérateur</th>
                    <th>Nombre de transferts</th>
                    <th>Total des commissions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($commissions === []): ?>
                    <tr>
                        <td colspan="3" class="muted">Aucune commission perçue.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($commissions as $commission): ?>
                        <tr>
                            <td><?= esc($commission['nom_operateur']) ?></td>
                            <td><?= esc($commission['nombre_transferts']) ?></td>
                            <td class="commission-amount">
                                <?= esc(number_format(
                                    (float) $commission['total_commission'],
                                    2,
                                    ',',
                                    ' '
                                )) ?> Ar
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?= $this->endSection() ?>
