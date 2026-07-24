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

    public function getMajor(Request $request): void
    {
        $facultyId = (int) $request->get('faculty_id', 0);
        $majorList = $this->lookup->getMajorByFaculty($facultyId);

        header('Content-Type: application/json');
        echo json_encode($majorList);
    }
}
