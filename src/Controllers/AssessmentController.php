<?php

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Middleware\AuthMiddleware;

class AssessmentController
{
    public function __construct()
    {
        AuthMiddleware::handle();
    }

    // GET /assessment — list available assessment types + user's past results
    public function index(Request $request): void
    {
        // TODO: AssessmentRepository::allTypes(), AssessmentRepository::resultsForUser($_SESSION['user_id'])
        Response::view('assessment/index', ['title' => 'Self-Assessment', 'assessments' => []]);
    }

    // GET /assessment/{id} — show the questionnaire form
    public function show(Request $request, string $id): void
    {
        // TODO: load questions for this assessment id
        Response::view('assessment/show', ['title' => 'Isi Assessment', 'id' => $id]);
    }

    // POST /assessment/{id} — score and save the submitted answers
    public function submit(Request $request, string $id): void
    {
        // TODO: score $request->all(), save result tied to $_SESSION['user_id']
        Response::redirect('/assessment');
    }
}
