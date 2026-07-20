<?php

namespace App\Controllers;

use App\Models\ConfigurationsCommission;
use CodeIgniter\Exceptions\PageNotFoundException;

class ConfigurationCommissionController extends BaseController
{
    public function index()
    {
        $model = new ConfigurationsCommission();

        return view('Operateur/config', [
            'title' => 'Configuration des commissions',
            'configurations' => $model->getConfigurations(),
        ]);
    }

    public function edit(int $id)
    {
        $model = new ConfigurationsCommission();
        $configurations = $model->getConfigurations();
        $configurationEdition = $this->findConfiguration($configurations, $id);

        if ($configurationEdition === null) {
            throw PageNotFoundException::forPageNotFound(
                'Configuration de commission introuvable.'
            );
        }

        return view('Operateur/config', [
            'title' => 'Modifier une commission',
            'configurations' => $configurations,
            'configurationEdition' => $configurationEdition,
        ]);
    }

    public function update(int $id)
    {
        $model = new ConfigurationsCommission();

        if ($model->find($id) === null) {
            throw PageNotFoundException::forPageNotFound(
                'Configuration de commission introuvable.'
            );
        }

        $data = [
            'pourcentage_commission' => $this->request->getPost('pourcentage_commission'),
        ];

        if (! $model->update($id, $data)) {
            return redirect()
                ->back()
                ->withInput()
                ->with('errors', $model->errors());
        }

        return redirect()
            ->to(site_url('operateur/commission'))
            ->with('success', 'La configuration de commission a été modifiée.');
    }

    private function findConfiguration(array $configurations, int $id): ?array
    {
        foreach ($configurations as $configuration) {
            if ((int) $configuration['id'] === $id) {
                return $configuration;
            }
        }

        return null;
    }
}
