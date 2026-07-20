<?= $this->extend('templates/layout') ?>

<?= $this->section('content') ?>
<div class="container">
    <div class="page-header">
        <h1>Historique des opérations</h1>
    </div>

    <?php if (session()->has('success')){ ?>
        <div class="alert alert-success">
            <?= esc(session('success')) ?>
        </div>
    <?php } ?>

    <?php if (session()->has('error')){ ?>
        <div class="alert alert-danger">
            <?= esc(session('error')) ?>
        </div>
    <?php } ?>

    <?php if (empty($historique)){ ?>
        <div class="empty-state">
            <h2>Aucune opération</h2>
            <p>Vous n’avez encore effectué aucune opération.</p>
            <a class="button" href="<?= site_url('client/operation') ?>">
                Faire une opération
            </a>
        </div>
    <?php } else { ?>
        <div class="table-wrapper">
            <table class="history-table">
                <thead>
                    <tr>
                        <th scope="col">Date</th>
                        <th scope="col">Opération</th>
                        <th scope="col">Sens</th>
                        <th scope="col">Correspondant</th>
                        <th scope="col" class="history-table__amount">Montant</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($historique as $operation){ ?>
                        <?php
                            $estRecu = $operation['sens'] === 'Recu';
                            $correspondant = '—';

                            if ($operation['type_operation'] === 'Transfert') {
                                $correspondant = $estRecu
                                    ? ($operation['emetteur'])
                                    : ($operation['beneficiaire']);
                            }
                        ?>
                        <tr>
                            <td data-label="Date"><?= esc($operation['date_operation']) ?></td>
                            <td data-label="Opération"><?= esc($operation['type_operation']) ?></td>
                            <td data-label="Sens">
                                <span class="status <?= $estRecu ? 'status--incoming' : 'status--outgoing' ?>">
                                    <?= $estRecu ? 'Reçu' : 'Envoyé' ?>
                                </span>
                            </td>
                            <td data-label="Correspondant"><?= esc($correspondant) ?></td>
                            <td data-label="Montant"
                                class="history-table__amount <?= $estRecu ? 'amount--positive' : 'amount--negative' ?>">
                                <?= $estRecu ? '+' : '-' ?>
                                <?= number_format((float) $operation['montant'], 2, ',', ' ') ?>&nbsp;Ar
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    <?php } ?>
</div>
<?= $this->endSection() ?>
