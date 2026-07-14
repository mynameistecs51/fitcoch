<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use App\Repositories\RoleRepository;
use App\Repositories\UserRepository;

class UserService
{
    public function __construct(
        private readonly UserRepository $userRepo,
        private readonly AuthorizationService $authzService,
    ) {
    }

    public function getCurrentUserProfile(int $userId): ?array
    {
        $user = $this->userRepo->findById($userId);

        if ($user === null) {
            return null;
        }

        return $this->buildProfileResponse($user);
    }

    /** @param array{first_name?: string, last_name?: string, timezone?: string} $data */
    public function updateProfile(int $userId, array $data): User
    {
        $errors = $this->validateProfileUpdate($data);

        if ($errors !== []) {
            throw new ValidationException('The provided inputs failed validation requirements.', $errors);
        }

        $user = $this->userRepo->updateProfile($userId, [
            'first_name' => trim((string) ($data['first_name'] ?? '')),
            'last_name' => trim((string) ($data['last_name'] ?? '')),
            'timezone' => (string) ($data['timezone'] ?? 'UTC'),
        ]);

        return $user;
    }

    /** @return array<string, mixed> */
    private function buildProfileResponse(User $user): array
    {
        return array_merge(
            $user->toPublicArray($this->authzService->getUserRoles($user->id)),
            [
                'timezone' => $user->timezone,
                'stats' => $this->defaultStats(),
            ]
        );
    }

    /** @return array<string, int> */
    private function defaultStats(): array
    {
        return [
            'current_streak' => 0,
            'longest_streak' => 0,
            'xp_balance' => 0,
            'level' => 1,
            'shields_count' => 0,
        ];
    }

    /** @return array<string, array<int, string>> */
    private function validateProfileUpdate(array $data): array
    {
        $errors = [];

        if (empty($data['first_name'])) {
            $errors['first_name'] = ['First name is required.'];
        }

        if (empty($data['last_name'])) {
            $errors['last_name'] = ['Last name is required.'];
        }

        if (empty($data['timezone']) || !in_array($data['timezone'], timezone_identifiers_list(), true)) {
            $errors['timezone'] = ['The provided timezone is invalid.'];
        }

        return $errors;
    }
}
