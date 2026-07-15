<?php

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Repositories\CounselorRepository;

class CounselorController
{
    private CounselorRepository $counselors;

    public function __construct()
    {
        $this->counselors = new CounselorRepository();
    }

    // GET /counselor — public
    public function index(Request $request): void
    {
        Response::view('counselor/index', [
            'title' => 'Konselor',
            'counselors' => $this->counselors->all(),
        ]);
    }

    // GET /counselor/{id} — public. $id is the counselor's users.id.
    public function show(Request $request, string $id): void
    {
        $counselor = $this->counselors->find((int) $id);

        if (!$counselor) {
            http_response_code(404);
            Response::view('errors/404', ['title' => 'Konselor Tidak Ditemukan']);
            return;
        }

        Response::view('counselor/show', [
            'title' => $counselor['nama'] ?: 'Detail Konselor',
            'counselor' => $counselor,
        ]);
    }
}
