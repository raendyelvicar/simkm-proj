<?php

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Repositories\ArticleRepository;

class HomeController
{
    public function index(Request $request): void
    {
        $articles = (new ArticleRepository())->latest(3);

        Response::view('home/index', [
            'title' => 'Sistem Informasi Manajemen Kesehatan Mental Student',
            'message' => '',
            'articles' => array_map(fn ($article) => $article->toArray(), $articles),
        ]);
    }
}
