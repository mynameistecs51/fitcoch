<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;
use App\Models\LiveAttendance;

class LiveAttendanceRepository implements RepositoryInterface
{
    public function __construct(private readonly Database $db)
    {
    }

    public function find(int $sessionId, int $userId): ?LiveAttendance
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM live_attendance
             WHERE live_session_id = :live_session_id AND user_id = :user_id
             LIMIT 1'
        );
        $stmt->execute([
            'live_session_id' => $sessionId,
            'user_id' => $userId,
        ]);
        $row = $stmt->fetch();

        return $row ? LiveAttendance::fromArray($row) : null;
    }

    public function join(int $sessionId, int $userId): LiveAttendance
    {
        $existing = $this->find($sessionId, $userId);

        if ($existing !== null && $existing->isActive()) {
            return $existing;
        }

        if ($existing !== null) {
            $stmt = $this->db->prepare(
                'UPDATE live_attendance
                 SET joined_at = CURRENT_TIMESTAMP,
                     left_at = NULL,
                     total_seconds = total_seconds
                 WHERE live_session_id = :live_session_id AND user_id = :user_id'
            );
            $stmt->execute([
                'live_session_id' => $sessionId,
                'user_id' => $userId,
            ]);

            return $this->find($sessionId, $userId)
                ?? throw new \RuntimeException('Failed to rejoin live session.');
        }

        $stmt = $this->db->prepare(
            'INSERT INTO live_attendance (live_session_id, user_id)
             VALUES (:live_session_id, :user_id)'
        );
        $stmt->execute([
            'live_session_id' => $sessionId,
            'user_id' => $userId,
        ]);

        return $this->find($sessionId, $userId)
            ?? throw new \RuntimeException('Failed to record live attendance.');
    }

    public function leave(int $sessionId, int $userId): LiveAttendance
    {
        $stmt = $this->db->prepare(
            'UPDATE live_attendance
             SET left_at = CURRENT_TIMESTAMP,
                 total_seconds = total_seconds + TIMESTAMPDIFF(SECOND, joined_at, CURRENT_TIMESTAMP)
             WHERE live_session_id = :live_session_id
               AND user_id = :user_id
               AND left_at IS NULL'
        );
        $stmt->execute([
            'live_session_id' => $sessionId,
            'user_id' => $userId,
        ]);

        return $this->find($sessionId, $userId)
            ?? throw new \RuntimeException('Failed to update live attendance.');
    }

    /** @return array<int, array<string, mixed>> */
    public function listRosterForSession(int $sessionId, int $cohortId): array
    {
        $stmt = $this->db->prepare(
            'SELECT
                ce.user_id,
                u.first_name,
                u.last_name,
                u.email,
                COALESCE(rt.status, \'locked\') AS ticket_status,
                la.joined_at,
                la.left_at,
                la.total_seconds
             FROM cohort_enrollments ce
             INNER JOIN users u ON u.id = ce.user_id
             LEFT JOIN readiness_tickets rt
                ON rt.user_id = ce.user_id
               AND rt.cohort_id = ce.cohort_id
               AND rt.module_id = (
                    SELECT module_id FROM live_sessions WHERE id = :session_id LIMIT 1
               )
             LEFT JOIN live_attendance la
                ON la.user_id = ce.user_id
               AND la.live_session_id = :session_id
             WHERE ce.cohort_id = :cohort_id
               AND ce.status = \'active\'
             ORDER BY u.first_name ASC, u.last_name ASC'
        );
        $stmt->execute([
            'session_id' => $sessionId,
            'cohort_id' => $cohortId,
        ]);

        return array_map(static function (array $row): array {
            $joinedAt = $row['joined_at'] ?? null;
            $leftAt = $row['left_at'] ?? null;
            $presence = 'not_joined';

            if ($joinedAt !== null && $leftAt === null) {
                $presence = 'online';
            } elseif ($joinedAt !== null) {
                $presence = 'left';
            }

            return [
                'user_id' => (int) $row['user_id'],
                'first_name' => (string) $row['first_name'],
                'last_name' => (string) $row['last_name'],
                'email' => (string) $row['email'],
                'ticket_status' => (string) $row['ticket_status'],
                'presence' => $presence,
                'joined_at' => $joinedAt,
                'left_at' => $leftAt,
                'total_seconds' => (int) ($row['total_seconds'] ?? 0),
            ];
        }, $stmt->fetchAll());
    }
}
