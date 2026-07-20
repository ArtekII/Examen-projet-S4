<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title ?? 'Espace client') ?></title>
    <link rel="stylesheet" href="<?= base_url('css/client.css') ?>">
</head>
<body>
    <?php if (session()->has('client_id')): ?>
        <?php $currentPath = trim(service('uri')->getPath(), '/'); ?>
        <nav class="navbar" aria-label="Navigation client">
            <div class="navbar__inner">
                <a class="navbar__brand" href="<?= site_url('client/compte') ?>">
                    Espace client
                </a>

                <ul class="navbar__links">
                    <li>
                        <a class="navbar__link <?= $currentPath === 'client/compte' ? 'navbar__link--active' : '' ?>"
                           href="<?= site_url('client/compte') ?>"
                           <?= $currentPath === 'client/compte' ? 'aria-current="page"' : '' ?>>
                            Voir le solde
                        </a>
                    </li>
                    <li>
                        <a class="navbar__link <?= $currentPath === 'client/operation' ? 'navbar__link--active' : '' ?>"
                           href="<?= site_url('client/operation') ?>"
                           <?= $currentPath === 'client/operation' ? 'aria-current="page"' : '' ?>>
                            Faire une opération
                        </a>
                    </li>
                    <li>
                        <a class="navbar__link <?= $currentPath === 'client/historique' ? 'navbar__link--active' : '' ?>"
                           href="<?= site_url('client/historique') ?>"
                           <?= $currentPath === 'client/historique' ? 'aria-current="page"' : '' ?>>
                            Historique
                        </a>
                    </li>
                </ul>

                <form class="navbar__logout-form" action="<?= site_url('client/deconnexion') ?>" method="post">
                    <?= csrf_field() ?>
                    <button class="navbar__logout" type="submit">Déconnexion</button>
                </form>
            </div>
        </nav>
    <?php endif; ?>

    <main class="page">
        <?= $this->renderSection('content') ?>
    </main>
</body>
</html>
