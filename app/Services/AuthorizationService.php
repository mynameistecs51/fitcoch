<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\RoleRepository;

class AuthorizationService
{
    public function __construct(private readonly RoleRepository $roleRepo)
    {
    }

    public function hasRole(int $userId, string $roleName): bool
    {
        return in_array($roleName, $this->getUserRoles($userId), true);
    }

    /** @param array<int, string> $roleNames */
    public function hasAnyRole(int $userId, array $roleNames): bool
    {
        $userRoles = $this->getUserRoles($userId);

        foreach ($roleNames as $roleName) {
            if (in_array($roleName, $userRoles, true)) {
                return true;
            }
        }

        return false;
    }

    /** @return array<int, string> */
    public function getUserRoles(int $userId): array
    {
        return $this->roleRepo->getRoleNamesForUser($userId);
    }
}
