<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;
use App\Models\Role;

class RoleRepository implements RepositoryInterface
{
    public function __construct(private readonly Database $db)
    {
    }

    public function findByName(string $name): ?Role
    {
        $stmt = $this->db->prepare('SELECT * FROM roles WHERE name = :name LIMIT 1');
        $stmt->execute(['name' => $name]);
        $row = $stmt->fetch();

        return $row ? Role::fromArray($row) : null;
    }

    /** @return array<int, string> */
    public function getRoleNamesForUser(int $userId): array
    {
        $stmt = $this->db->prepare(
            'SELECT r.name
             FROM user_roles ur
             JOIN roles r ON ur.role_id = r.id
             WHERE ur.user_id = :user_id
             ORDER BY r.name'
        );
        $stmt->execute(['user_id' => $userId]);

        return array_map('strval', $stmt->fetchAll(\PDO::FETCH_COLUMN));
    }

    public function assignRole(int $userId, string $roleName): void
    {
        $role = $this->findByName($roleName);

        if ($role === null) {
            throw new \RuntimeException("Role [{$roleName}] does not exist.");
        }

        $stmt = $this->db->prepare(
            'INSERT IGNORE INTO user_roles (user_id, role_id) VALUES (:user_id, :role_id)'
        );
        $stmt->execute([
            'user_id' => $userId,
            'role_id' => $role->id,
        ]);
    }

    public function removeRole(int $userId, string $roleName): void
    {
        $role = $this->findByName($roleName);

        if ($role === null) {
            return;
        }

        $stmt = $this->db->prepare(
            'DELETE FROM user_roles WHERE user_id = :user_id AND role_id = :role_id'
        );
        $stmt->execute([
            'user_id' => $userId,
            'role_id' => $role->id,
        ]);
    }

    /** @return array<int, Role> */
    public function listAll(): array
    {
        $stmt = $this->db->prepare('SELECT * FROM roles ORDER BY id');
        $stmt->execute();

        return array_map(
            static fn (array $row): Role => Role::fromArray($row),
            $stmt->fetchAll()
        );
    }

    /** @param array<int, string> $roleNames */
    public function syncRoles(int $userId, array $roleNames): void
    {
        $this->db->pdo()->beginTransaction();

        try {
            $stmt = $this->db->prepare('DELETE FROM user_roles WHERE user_id = :user_id');
            $stmt->execute(['user_id' => $userId]);

            foreach ($roleNames as $roleName) {
                $this->assignRole($userId, $roleName);
            }

            $this->db->pdo()->commit();
        } catch (\Throwable $e) {
            $this->db->pdo()->rollBack();
            throw $e;
        }
    }
}
