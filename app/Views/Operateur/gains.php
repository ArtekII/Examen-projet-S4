<?= $this->extend('templates/layout_admin') ?>

<?= $this->section('content') ?>
<div class="admin-page">
    <div class="admin-page__header">
        <h1>Gains sur les frais</h1>
        <p>Consultez les frais perçus pour chaque opération.</p>
    </div>

    <form class="admin-filter" method="get" action="<?= site_url('operateur/gains') ?>">
        <div class="admin-form__group">
            <label for="id_type_operation">Type d’opération</label>
            <select name="id_type_operation" id="id_type_operation">
                <option value="">Tous</option>
                <?php foreach ($types as $type): ?>
                    <option value="<?= esc($type['id']) ?>"
                        <?= $selectedType == $type['id'] ? 'selected' : '' ?>>
                        <?= esc($type['libelle']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <button class="admin-button" type="submit">Filtrer</button>
    </form>

    <div class="table-wrapper">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>ID opération</th>
                    <th>Date</th>
                    <th>Type</th>
                    <th>Montant</th>
                    <th>Frais perçus</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($gains as $gain): ?>
                    <tr>
                        <td><?= esc($gain['nom_client']) ?></td>
                        <td><?= esc($gain['id_operation']) ?></td>
                        <td><?= esc($gain['date_operation']) ?></td>
                        <td><?= esc($gain['type_operation']) ?></td>
                        <td><?= esc($gain['montant']) ?> Ar</td>
                        <td>
                            <?php if ($gain['montant_frais'] !== null): ?>
                                <?= esc($gain['montant_frais']) ?> Ar
                            <?php else: ?>
                                <span class="muted">Aucune configuration</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div class="total-box">
        <?php if (! empty($selectedType)): ?>
            Total pour ce type : <?= esc($totalFiltre) ?> Ar
        <?php else: ?>
            Total des gains : <?= esc($total['total_gains'] ?? 0) ?> Ar
        <?php endif; ?>
    </div>
</div>
<?= $this->endSection() ?>
