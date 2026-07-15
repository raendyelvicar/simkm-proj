<?php

namespace App\Controllers;

use App\Core\Request;
use App\Repositories\LookupRepository;

class LookupController
{
    private LookupRepository $lookup;

    public function __construct()
    {
        $this->lookup = new LookupRepository();
    }

    public function getJurusan(Request $request): void
    {
        $fakultasId = (int) $request->get('fakultas_id', 0);
        $jurusanList = $this->lookup->getJurusanByFakultas($fakultasId);

        header('Content-Type: application/json');
        echo json_encode($jurusanList);
    }
}
