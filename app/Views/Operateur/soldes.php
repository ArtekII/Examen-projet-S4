<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Soldes des clients</title>
    <style>
        body { font-family: sans-serif; max-width: 800px; margin: 40px auto; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ccc; padding: 8px; text-align: left; }
        th { background: #f2f2f2; }
    </style>
</head>
<body>

    <h1>Soldes des clients</h1>

    <table>
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
                    <td><?= esc($solde['SOLDE']) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
                <button><a href="/operateur/gains">Gains</a></button>
</body>
</html>