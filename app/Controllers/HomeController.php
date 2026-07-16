<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Services\AuthService;
use App\Services\HomeService;

class HomeController
{
    public function __construct(
        private readonly AuthService $authService,
        private readonly HomeService $homeService,
    ) {
    }

    public function index(Request $request): Response
    {
        if ($this->authService->currentUser() !== null) {
            return Response::redirect('/dashboard');
        }

        $searchQuery = trim((string) ($request->query()['q'] ?? ''));
        $landing = $this->homeService->buildLandingData($searchQuery !== '' ? $searchQuery : null);

        return Response::view('home/landing', [
            'title' => __('home.title'),
            'landing' => $landing,
        ]);
    }
}
