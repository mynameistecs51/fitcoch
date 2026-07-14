<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Services\LocaleService;

class LocaleController
{
    public function switch(Request $request, string $locale): Response
    {
        LocaleService::set($locale);

        $redirect = $_SERVER['HTTP_REFERER'] ?? '';
        $appBase = url('/');

        if (!is_string($redirect) || $redirect === '' || !str_contains($redirect, rtrim($appBase, '/'))) {
            $redirect = url('/login');
        }

        return Response::redirect($redirect);
    }
}
