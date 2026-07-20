<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gains sur frais</title>
    <style>
        body { font-family: sans-serif; max-width: 900px; margin: 40px auto; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ccc; padding: 8px; text-align: left; }
        th { background: #f2f2f2; }
        .total-box {
            margin-top: 20px;
            padding: 15px;
            background: #e8f5e9;
            border: 1px solid #4caf50;
            font-size: 1.2em;
            font-weight: bold;
        }
        .no-frais { color: #999; font-style: italic; }
        .filtre { margin-bottom: 20px; }
        select { padding: 6px; }
    </style>
</head>
<body>

    <h1>Gains sur frais de transaction</h1>

    <form method="get" action="/operateur/gains" class="filtre">
        <label for="id_type_operation">Filtrer par type d'opération :</label>
        <select name="id_type_operation" id="id_type_operation" onchange="this.form.submit()">
            <option value="">-- Tous --</option>
            <?php foreach ($types as $type): ?>
                <option value="<?= esc($type['id']) ?>"
                    <?= ($selectedType == $type['id']) ? 'selected' : '' ?>>
                    <?= esc($type['libelle']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </form>

    <table>
        <thead>
            <tr>
                <th>ID Opération</th>
                <th>Date</th>
                <th>Type</th>
                <th>Montant</th>
                <th>Frais perçus</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($gains as $gain): ?>
                <tr>
                    <td><?= esc($gain['id_operation']) ?></td>
                    <td><?= esc($gain['date_operation']) ?></td>
                    <td><?= esc($gain['type_operation']) ?></td>
                    <td><?= esc($gain['montant']) ?></td>
                    <td>
                        <?php if ($gain['montant_frais'] !== null): ?>
                            <?= esc($gain['montant_frais']) ?>
                        <?php else: ?>
                            <span class="no-frais">Aucune configuration</span>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

 <div class="total-box">
    <?php if (!empty($selectedType)): ?>
        Total des gains pour ce type : <?= esc($totalFiltre) ?> Ar
    <?php else: ?>
        Total des gains (tous types) : <?= esc($total['total_gains']) ?> Ar
    <?php endif; ?>
</div>

</body>
</html>