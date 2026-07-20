<?= $this->extend('templates/layout_admin') ?>

<?= $this->section('content') ?>
<?php
$errors = session('errors') ?? [];
$success = session('success');
?>

<div class="admin-page">
    <div class="admin-page__header">
        <h1>Configuration des commissions</h1>
        <p>Consultez et modifiez le pourcentage de commission de chaque opérateur.</p>
    </div>

    <?php if ($success): ?>
        <div class="admin-alert admin-alert--success" role="status">
            <?= esc($success) ?>
        </div>
    <?php endif; ?>

    <?php if (isset($configurationEdition)): ?>
        <form
            class="admin-form"
            method="post"
            action="<?= site_url('operateur/commission/' . $configurationEdition['id']) ?>"
        >
            <?= csrf_field() ?>

            <div class="admin-form__group">
                <label for="nom_operateur">Opérateur</label>
                <input
                    type="text"
                    id="nom_operateur"
                    value="<?= esc($configurationEdition['nom_operateur']) ?>"
                    disabled
                >
            </div>

            <div class="admin-form__group">
                <label for="pourcentage_commission">Pourcentage de commission</label>
                <input
                    type="number"
                    name="pourcentage_commission"
                    id="pourcentage_commission"
                    value="<?= esc(old(
                        'pourcentage_commission',
                        $configurationEdition['pourcentage_commission']
                    )) ?>"
                    min="0"
                    max="100"
                    step="0.01"
                    required
                >
                <div class="error">
                    <?= esc($errors['pourcentage_commission'] ?? '') ?>
                </div>
            </div>

            <div class="admin-actions">
                <button class="admin-button" type="submit">Enregistrer</button>
                <a class="admin-button admin-button--secondary" href="<?= site_url('operateur/commission') ?>">
                    Annuler
                </a>
            </div>
        </form>
    <?php endif; ?>

    <div class="table-wrapper">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Opérateur</th>
                    <th>Pourcentage de commission</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($configurations === []): ?>
                    <tr>
                        <td colspan="4" class="muted">Aucune configuration de commission.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($configurations as $configuration): ?>
                        <tr>
                            <td><?= esc($configuration['id']) ?></td>
                            <td><?= esc($configuration['nom_operateur']) ?></td>
                            <td><?= esc($configuration['pourcentage_commission']) ?> %</td>
                            <td>
                                <a
                                    class="admin-button admin-button--small"
                                    href="<?= site_url(
                                        'operateur/commission/' . $configuration['id'] . '/edit'
                                    ) ?>"
                                >
                                    Modifier
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?= $this->endSection() ?>
