<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configurations Transaction</title>
    <style>
        body { font-family: sans-serif; max-width: 800px; margin: 40px auto; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ccc; padding: 8px; text-align: left; }
        div.error { color: red; font-size: 0.9em; }
        label { display: block; margin-top: 10px; }
        input, select { padding: 6px; width: 100%; box-sizing: border-box; }
        button { margin-top: 15px; padding: 8px 16px; }
    </style>
</head>
<body>

    <h1>Configurations Transaction</h1>

    <form method="post" action="/operateur/store">
        <?= csrf_field() ?>

        <label for="borne_min">Borne minimale</label>
        <input type="text" name="borne_min" id="borne_min" placeholder="Borne minimale">
        <div class="error"><?= $validation['borne_min'] ?? '' ?></div>

        <label for="borne_max">Borne maximale</label>
        <input type="text" name="borne_max" id="borne_max" placeholder="Borne maximale">
        <div class="error"><?= $validation['borne_max'] ?? '' ?></div>

        <label for="montant_frais">Montant des frais</label>
        <input type="text" name="montant_frais" id="montant_frais" placeholder="Montant des frais">
        <div class="error"><?= $validation['montant_frais'] ?? '' ?></div>

        <label for="id_type_operation">Type d'opération</label>
        <select name="id_type_operation" id="id_type_operation">
            <option value="">-- Sélectionner --</option>
            <?php foreach ($types as $type): ?>
                <option value="<?= esc($type['id']) ?>">
                    <?= esc($type['libelle']) ?>
                </option>
            <?php endforeach; ?>
        </select>
        <div class="error"><?= $validation['id_type_operation'] ?? '' ?></div>

        <button type="submit">Envoyer</button>
    </form>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Borne min</th>
                <th>Borne max</th>
                <th>Montant frais</th>
                <th>Type opération</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($configurations as $config): ?>
                <tr>
                    <td><?= esc($config['id']) ?></td>
                    <td><?= esc($config['borne_min']) ?></td>
                    <td><?= esc($config['borne_max']) ?></td>
                    <td><?= esc($config['montant_frais']) ?></td>
                    <td><?= esc($config['id_type_operation']) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

</body>
</html>