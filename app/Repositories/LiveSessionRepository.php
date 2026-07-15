<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;
use App\Models\LiveSession;

class LiveSessionRepository implements RepositoryInterface
{
    public function __construct(private readonly Database $db)
    {
    }

    public function findById(int $id): ?LiveSession
    {
        $stmt = $this->db->prepare('SELECT * FROM live_sessions WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();

        return $row ? LiveSession::fromArray($row) : null;
    }

    public function findByRoomId(string $roomId): ?LiveSession
    {
        $stmt = $this->db->prepare('SELECT * FROM live_sessions WHERE room_id = :room_id LIMIT 1');
        $stmt->execute(['room_id' => $roomId]);
        $row = $stmt->fetch();

        return $row ? LiveSession::fromArray($row) : null;
    }

    /** @return array<int, LiveSession> */
    public function listByModule(int $moduleId): array
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM live_sessions
             WHERE module_id = :module_id
             ORDER BY start_time DESC'
        );
        $stmt->execute(['module_id' => $moduleId]);

        return array_map(
            static fn (array $row): LiveSession => LiveSession::fromArray($row),
            $stmt->fetchAll()
        );
    }

    /** @return array<int, LiveSession> */
    public function listByModuleIds(array $moduleIds): array
    {
        if ($moduleIds === []) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($moduleIds), '?'));
        $stmt = $this->db->prepare(
            "SELECT * FROM live_sessions
             WHERE module_id IN ({$placeholders})
             ORDER BY start_time DESC"
        );
        $stmt->execute(array_values($moduleIds));

        return array_map(
            static fn (array $row): LiveSession => LiveSession::fromArray($row),
            $stmt->fetchAll()
        );
    }

    /** @param array{cohort_id: int, module_id: int, title: string, start_time: string, end_time: string, status: string, room_id: string} $data */
    public function create(array $data): LiveSession
    {
        $stmt = $this->db->prepare(
            'INSERT INTO live_sessions (cohort_id, module_id, title, start_time, end_time, status, room_id)
             VALUES (:cohort_id, :module_id, :title, :start_time, :end_time, :status, :room_id)'
        );
        $stmt->execute($data);

        $session = $this->findById((int) $this->db->lastInsertId());

        if ($session === null) {
            throw new \RuntimeException('Failed to create live session.');
        }

        return $session;
    }

    public function updateStatus(int $sessionId, string $status): LiveSession
    {
        $stmt = $this->db->prepare(
            'UPDATE live_sessions SET status = :status WHERE id = :id'
        );
        $stmt->execute([
            'id' => $sessionId,
            'status' => $status,
        ]);

        $session = $this->findById($sessionId);

        if ($session === null) {
            throw new \RuntimeException('Failed to update live session status.');
        }

        return $session;
    }

    public function roomIdExists(string $roomId): bool
    {
        $stmt = $this->db->prepare('SELECT 1 FROM live_sessions WHERE room_id = :room_id LIMIT 1');
        $stmt->execute(['room_id' => $roomId]);

        return (bool) $stmt->fetchColumn();
    }
}
