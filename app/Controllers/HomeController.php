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
        $searchQuery = trim((string) ($request->query()['q'] ?? ''));
        $landing = $this->homeService->buildLandingData($searchQuery !== '' ? $searchQuery : null);
        $user = $this->authService->currentUser();
        $roles = $user !== null ? $this->authService->getUserRoles($user->id) : [];

        return Response::view('home/landing', [
            'title' => __('home.title'),
            'landing' => $landing,
            'user' => $user,
            'roles' => $roles,
        ]);
    }
}