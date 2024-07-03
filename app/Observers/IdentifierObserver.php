<?php

namespace App\Observers;

use Domains\Totem\Models\Totem;

class IdentifierObserver
{

    public function creating(Totem $model)
    {
        $lastModel = Totem::orderBy('identificador', 'desc')->first();
        $model->identificador = 'T' . sprintf('%03d', $lastModel ? (substr($lastModel->identificador, 1) + 1) : 1);
    }
}
