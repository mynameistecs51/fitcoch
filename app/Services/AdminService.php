<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\RoleRepository;
use App\Repositories\UserRepository;
use Exception;

class AdminService
{
    private const ALLOWED_ROLES = ['learner', 'instructor', 'admin'];

    public function __construct(
        private readonly UserRepository $userRepo,
        private readonly RoleRepository $roleRepo,
    ) {
    }

    /** @return array<int, array{user: \App\Models\User, roles: array<int, string>}> */
    public function listAccounts(): array
    {
        return $this->userRepo->listWithRoles();
    }

    /** @return array<int, \App\Models\Role> */
    public function listAvailableRoles(): array
    {
        return $this->roleRepo->listAll();
    }

    /** @param array<int, string> $roleNames */
    public function updateUserRoles(int $actorId, int $targetUserId, array $roleNames): void
    {
        $normalized = $this->normalizeRoles($roleNames);

        if ($normalized === []) {
            throw new ValidationException(__('errors.validation_failed'), [
                'roles' => [__('admin.validation.roles_required')],
            ]);
        }

        if ($actorId === $targetUserId && !in_array('admin', $normalized, true)) {
            throw new ValidationException(__('errors.validation_failed'), [
                'roles' => [__('admin.validation.cannot_remove_own_admin')],
            ]);
        }

        $target = $this->userRepo->findById($targetUserId);

        if ($target === null) {
            throw new Exception(__('admin.validation.user_not_found'));
        }

        $this->roleRepo->syncRoles($targetUserId, $normalized);
    }

    public function updateUserStatus(int $actorId, int $targetUserId, string $status): void
    {
        if ($actorId === $targetUserId) {
            throw new ValidationException(__('errors.validation_failed'), [
                'status' => [__('admin.validation.cannot_suspend_self')],
            ]);
        }

        if (!in_array($status, ['active', 'suspended'], true)) {
            throw new ValidationException(__('errors.validation_failed'), [
                'status' => [__('admin.validation.invalid_status')],
            ]);
        }

        $target = $this->userRepo->findById($targetUserId);

        if ($target === null) {
            throw new Exception(__('admin.validation.user_not_found'));
        }

        $this->userRepo->updateStatus($targetUserId, $status);
    }

    /** @param array<int, string> $roleNames */
    private function normalizeRoles(array $roleNames): array
    {
        $normalized = [];

        foreach ($roleNames as $roleName) {
            if (!in_array($roleName, self::ALLOWED_ROLES, true)) {
                continue;
            }

            $normalized[] = $roleName;
        }

        sort($normalized);

        return array_values(array_unique($normalized));
    }
}
