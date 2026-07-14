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

    /** @param array<string, mixed> $data */
    public function updateUserAccount(int $targetUserId, array $data): void
    {
        $target = $this->userRepo->findById($targetUserId);

        if ($target === null) {
            throw new Exception(__('admin.validation.user_not_found'));
        }

        $validated = $this->validateAccountData($data, $targetUserId);
        $this->userRepo->updateAccount($targetUserId, $validated);
    }

    /** @param array<string, mixed> $data */
    private function validateAccountData(array $data, int $userId): array
    {
        $errors = [];
        $firstName = trim((string) ($data['first_name'] ?? ''));
        $lastName = trim((string) ($data['last_name'] ?? ''));
        $email = strtolower(trim((string) ($data['email'] ?? '')));

        if ($firstName === '') {
            $errors['first_name'][] = __('validation.first_name_required');
        }

        if ($lastName === '') {
            $errors['last_name'][] = __('validation.last_name_required');
        }

        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'][] = __('validation.email_required');
        } elseif ($this->userRepo->emailExistsForOtherUser($email, $userId)) {
            $errors['email'][] = __('validation.email_taken');
        }

        if ($errors !== []) {
            throw new ValidationException(__('errors.validation_failed'), $errors);
        }

        return [
            'first_name' => $firstName,
            'last_name' => $lastName,
            'email' => $email,
        ];
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
