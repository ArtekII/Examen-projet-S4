<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Commissions par opérateur</title>
    <style>
        body { font-family: sans-serif; max-width: 800px; margin: 40px auto; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ccc; padding: 8px; text-align: left; }
        th { background: #f2f2f2; }
    </style>
</head>
<body>

    <h1>Commissions perçues par opérateur</h1>

    <table>
        <thead>
            <tr>
                <th>Opérateur</th>
                <th>Nombre de transferts</th>
                <th>Total commission</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($commissions as $c): ?>
                <tr>
                    <td><?= esc($c['nom_operateur']) ?></td>
                    <td><?= esc($c['nombre_transferts']) ?></td>
                    <td><?= esc($c['total_commission']) ?> Ar</td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

</body>
</html>