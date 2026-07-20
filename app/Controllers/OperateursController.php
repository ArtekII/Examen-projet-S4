<?php

namespace App\Controllers;

use App\Models\Operateurs;
use CodeIgniter\Exceptions\PageNotFoundException;

class OperateursController extends BaseController
{
    public function index()
    {
        $model = new Operateurs();
        $ourOperatorId = $this->getOurOperatorId();

        return view('Operateur/prefixe', [
            'title' => 'Configuration des préfixes',
            'operateurs' => $model->getOthersOrderedByName($ourOperatorId),
        ]);
    }

    public function edit(int $id)
    {
        $model = new Operateurs();
        $ourOperatorId = $this->getOurOperatorId();

        if ($id === $ourOperatorId) {
            throw PageNotFoundException::forPageNotFound(
                'Opérateur introuvable.'
            );
        }

        $operateurEdition = $model->find($id);

        if ($operateurEdition === null) {
            throw PageNotFoundException::forPageNotFound(
                'Opérateur introuvable.'
            );
        }

        return view('Operateur/prefixe', [
            'title' => 'Modifier un préfixe',
            'operateurs' => $model->getOthersOrderedByName($ourOperatorId),
            'operateurEdition' => $operateurEdition,
        ]);
    }

    public function update(int $id)
    {
        $model = new Operateurs();

        if ($id === $this->getOurOperatorId() || $model->find($id) === null) {
            throw PageNotFoundException::forPageNotFound(
                'Opérateur introuvable.'
            );
        }

        $prefixe = trim((string) $this->request->getPost('prefixe'));

        if ($model->prefixeUtiliseParUnAutre($prefixe, $id)) {
            return redirect()
                ->back()
                ->withInput()
                ->with('errors', [
                    'prefixe' => 'Ce préfixe est déjà utilisé par un autre opérateur.',
                ]);
        }

        if (! $model->update($id, ['prefixe' => $prefixe])) {
            return redirect()
                ->back()
                ->withInput()
                ->with('errors', $model->errors());
        }

        return redirect()
            ->to(site_url('operateur/prefixe'))
            ->with('success', 'Le préfixe de l’opérateur a été modifié.');
    }

    private function getOurOperatorId(): int
    {
        return (int) config('MobileMoney')->operatorId;
    }
}
