<?= $this->extend('templates/layout_admin') ?>

<?= $this->section('content') ?>
<div class="admin-page">
    <div class="admin-page__header">
        <h1>Configuration des frais</h1>
        <p>Définissez les frais selon le montant et le type d’opération.</p>
    </div>

    <form class="admin-form" method="post" action="<?= site_url('operateur/store') ?>">
        <?= csrf_field() ?>

        <div class="admin-form__group">
            <label for="borne_min">Borne minimale</label>
            <input type="number" name="borne_min" id="borne_min" value="<?= esc(old('borne_min')) ?>"
                   min="0" step="0.01" required>
            <div class="error"><?= esc($validation['borne_min'] ?? '') ?></div>
        </div>

        <div class="admin-form__group">
            <label for="borne_max">Borne maximale</label>
            <input type="number" name="borne_max" id="borne_max" value="<?= esc(old('borne_max')) ?>"
                   min="0" step="0.01" required>
            <div class="error"><?= esc($validation['borne_max'] ?? '') ?></div>
        </div>

        <div class="admin-form__group">
            <label for="montant_frais">Montant des frais</label>
            <input type="number" name="montant_frais" id="montant_frais"
                   value="<?= esc(old('montant_frais')) ?>" min="0" step="0.01" required>
            <div class="error"><?= esc($validation['montant_frais'] ?? '') ?></div>
        </div>

        <div class="admin-form__group">
            <label for="id_type_operation">Type d’opération</label>
            <select name="id_type_operation" id="id_type_operation" required>
                <option value="">Sélectionner</option>
                <?php foreach ($types as $type): ?>
                    <option value="<?= esc($type['id']) ?>"
                        <?= old('id_type_operation') == $type['id'] ? 'selected' : '' ?>>
                        <?= esc($type['libelle']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <div class="error"><?= esc($validation['id_type_operation'] ?? '') ?></div>
        </div>

        <button class="admin-button" type="submit">Enregistrer</button>
    </form>

    <div class="table-wrapper">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Borne minimale</th>
                    <th>Borne maximale</th>
                    <th>Montant des frais</th>
                    <th>Type d’opération</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($configurations as $config): ?>
                    <tr>
                        <td><?= esc($config['id']) ?></td>
                        <td><?= esc($config['borne_min']) ?> Ar</td>
                        <td><?= esc($config['borne_max']) ?> Ar</td>
                        <td><?= esc($config['montant_frais']) ?> Ar</td>
                        <td><?= esc($config['id_type_operation']) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?= $this->endSection() ?>
