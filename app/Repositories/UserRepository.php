<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;
use App\Models\User;

class UserRepository implements RepositoryInterface
{
    public function __construct(private readonly Database $db)
    {
    }

    public function findById(int $id): ?User
    {
        $stmt = $this->db->prepare('SELECT * FROM users WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();

        return $row ? User::fromArray($row) : null;
    }

    public function findByEmail(string $email): ?User
    {
        $stmt = $this->db->prepare('SELECT * FROM users WHERE email = :email LIMIT 1');
        $stmt->execute(['email' => $email]);
        $row = $stmt->fetch();

        return $row ? User::fromArray($row) : null;
    }

    public function emailExists(string $email): bool
    {
        $stmt = $this->db->prepare('SELECT 1 FROM users WHERE email = :email LIMIT 1');
        $stmt->execute(['email' => $email]);

        return (bool) $stmt->fetchColumn();
    }

    /** @param array{email: string, password_hash: string, first_name: string, last_name: string, timezone: string} $data */
    public function create(array $data): User
    {
        $stmt = $this->db->prepare(
            'INSERT INTO users (email, password_hash, first_name, last_name, timezone)
             VALUES (:email, :password_hash, :first_name, :last_name, :timezone)'
        );

        $stmt->execute([
            'email' => $data['email'],
            'password_hash' => $data['password_hash'],
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'timezone' => $data['timezone'],
        ]);

        $user = $this->findById((int) $this->db->lastInsertId());

        if ($user === null) {
            throw new \RuntimeException('Failed to create user record.');
        }

        return $user;
    }

    /** @param array{first_name: string, last_name: string, timezone: string} $data */
    public function updateProfile(int $userId, array $data): User
    {
        $stmt = $this->db->prepare(
            'UPDATE users
             SET first_name = :first_name, last_name = :last_name, timezone = :timezone
             WHERE id = :id'
        );

        $stmt->execute([
            'id' => $userId,
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'timezone' => $data['timezone'],
        ]);

        $user = $this->findById($userId);

        if ($user === null) {
            throw new \RuntimeException('Failed to update user profile.');
        }

        return $user;
    }

    /** @return array<int, array{user: User, roles: array<int, string>}> */
    public function listWithRoles(): array
    {
        $stmt = $this->db->prepare('SELECT * FROM users ORDER BY id ASC');
        $stmt->execute();
        $rows = $stmt->fetchAll();

        $users = [];

        foreach ($rows as $row) {
            $user = User::fromArray($row);
            $roleStmt = $this->db->prepare(
                'SELECT r.name
                 FROM user_roles ur
                 JOIN roles r ON ur.role_id = r.id
                 WHERE ur.user_id = :user_id
                 ORDER BY r.name'
            );
            $roleStmt->execute(['user_id' => $user->id]);
            $roles = array_map('strval', $roleStmt->fetchAll(\PDO::FETCH_COLUMN));

            $users[] = [
                'user' => $user,
                'roles' => $roles,
            ];
        }

        return $users;
    }

    public function updateStatus(int $userId, string $status): User
    {
        if (!in_array($status, ['active', 'suspended'], true)) {
            throw new \InvalidArgumentException('Invalid user status.');
        }

        $stmt = $this->db->prepare('UPDATE users SET status = :status WHERE id = :id');
        $stmt->execute([
            'id' => $userId,
            'status' => $status,
        ]);

        $user = $this->findById($userId);

        if ($user === null) {
            throw new \RuntimeException('Failed to update user status.');
        }

        return $user;
    }
}
