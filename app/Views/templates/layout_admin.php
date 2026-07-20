<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title ?? 'Administration') ?></title>
    <link rel="stylesheet" href="<?= base_url('css/admin.css') ?>">
</head>
<body>
    <?php $currentPath = trim(service('uri')->getPath(), '/'); ?>

    <div class="admin-layout">
        <aside class="sidebar">
            <a class="sidebar__brand" href="<?= site_url('operateur') ?>">
                Administration
            </a>

            <nav aria-label="Navigation administrateur">
                <ul class="sidebar__links">
                    <li>
                        <a class="sidebar__link <?= $currentPath === 'operateur' ? 'sidebar__link--active' : '' ?>"
                           href="<?= site_url('operateur') ?>"
                           <?= $currentPath === 'operateur' ? 'aria-current="page"' : '' ?>>
                            Configuration des frais
                        </a>
                    </li>
                    <li>
                        <a class="sidebar__link <?= $currentPath === 'operateur/commission' ? 'sidebar__link--active' : '' ?>"
                           href="<?= site_url('operateur/commission') ?>"
                           <?= $currentPath === 'operateur/commission' ? 'aria-current="page"' : '' ?>>
                            Configuration des commission
                        </a>
                    </li>
                    <li>
                        <a class="sidebar__link <?= $currentPath === 'operateur/soldes' ? 'sidebar__link--active' : '' ?>"
                           href="<?= site_url('operateur/soldes') ?>"
                           <?= $currentPath === 'operateur/soldes' ? 'aria-current="page"' : '' ?>>
                            Soldes des clients
                        </a>
                    </li>
                    <li>
                        <a class="sidebar__link <?= $currentPath === 'operateur/gains' ? 'sidebar__link--active' : '' ?>"
                           href="<?= site_url('operateur/gains') ?>"
                           <?= $currentPath === 'operateur/gains' ? 'aria-current="page"' : '' ?>>
                            Gains sur les frais
                        </a>
                    </li>
                    <li>
                        <a class="sidebar__link <?= $currentPath === 'operateur/prefixe' ? 'sidebar__link--active' : '' ?>"
                           href="<?= site_url('operateur/prefixe') ?>"
                           <?= $currentPath === 'operateur/prefixe' ? 'aria-current="page"' : '' ?>>
                            Gestion des préfixes
                        </a>
                    </li>
                </ul>
            </nav>
        </aside>

        <main class="admin-content">
            <?= $this->renderSection('content') ?>
        </main>
    </div>
</body>
</html>
