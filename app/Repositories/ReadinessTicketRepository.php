<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;
use App\Models\ReadinessTicket;

class ReadinessTicketRepository implements RepositoryInterface
{
    public function __construct(private readonly Database $db)
    {
    }

    public function find(int $userId, int $cohortId, int $moduleId): ?ReadinessTicket
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM readiness_tickets
             WHERE user_id = :user_id AND cohort_id = :cohort_id AND module_id = :module_id
             LIMIT 1'
        );
        $stmt->execute([
            'user_id' => $userId,
            'cohort_id' => $cohortId,
            'module_id' => $moduleId,
        ]);
        $row = $stmt->fetch();

        return $row ? ReadinessTicket::fromArray($row) : null;
    }

    public function ensureLocked(int $userId, int $cohortId, int $moduleId): ReadinessTicket
    {
        $existing = $this->find($userId, $cohortId, $moduleId);

        if ($existing !== null) {
            return $existing;
        }

        $stmt = $this->db->prepare(
            'INSERT INTO readiness_tickets (user_id, cohort_id, module_id, status)
             VALUES (:user_id, :cohort_id, :module_id, \'locked\')'
        );
        $stmt->execute([
            'user_id' => $userId,
            'cohort_id' => $cohortId,
            'module_id' => $moduleId,
        ]);

        return $this->find($userId, $cohortId, $moduleId)
            ?? throw new \RuntimeException('Failed to create readiness ticket.');
    }

    public function unlock(int $userId, int $cohortId, int $moduleId): ReadinessTicket
    {
        $this->ensureLocked($userId, $cohortId, $moduleId);

        $stmt = $this->db->prepare(
            'UPDATE readiness_tickets
             SET status = \'unlocked\',
                 unlocked_at = COALESCE(unlocked_at, CURRENT_TIMESTAMP),
                 overridden_by = NULL,
                 overridden_at = NULL
             WHERE user_id = :user_id AND cohort_id = :cohort_id AND module_id = :module_id'
        );
        $stmt->execute([
            'user_id' => $userId,
            'cohort_id' => $cohortId,
            'module_id' => $moduleId,
        ]);

        return $this->find($userId, $cohortId, $moduleId)
            ?? throw new \RuntimeException('Failed to unlock readiness ticket.');
    }

    public function override(int $userId, int $cohortId, int $moduleId, int $instructorId): ReadinessTicket
    {
        $this->ensureLocked($userId, $cohortId, $moduleId);

        $stmt = $this->db->prepare(
            'UPDATE readiness_tickets
             SET status = \'overridden\',
                 overridden_by = :overridden_by,
                 overridden_at = CURRENT_TIMESTAMP,
                 unlocked_at = COALESCE(unlocked_at, CURRENT_TIMESTAMP)
             WHERE user_id = :user_id AND cohort_id = :cohort_id AND module_id = :module_id'
        );
        $stmt->execute([
            'user_id' => $userId,
            'cohort_id' => $cohortId,
            'module_id' => $moduleId,
            'overridden_by' => $instructorId,
        ]);

        return $this->find($userId, $cohortId, $moduleId)
            ?? throw new \RuntimeException('Failed to override readiness ticket.');
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function listEnrollmentStatuses(int $cohortId, int $moduleId): array
    {
        $stmt = $this->db->prepare(
            'SELECT
                ce.user_id,
                u.first_name,
                u.last_name,
                u.email,
                COALESCE(rt.status, \'locked\') AS status,
                rt.unlocked_at,
                rt.overridden_at,
                rt.overridden_by
             FROM cohort_enrollments ce
             INNER JOIN users u ON u.id = ce.user_id
             LEFT JOIN readiness_tickets rt
                ON rt.user_id = ce.user_id
               AND rt.cohort_id = ce.cohort_id
               AND rt.module_id = :module_id
             WHERE ce.cohort_id = :cohort_id
               AND ce.status = \'active\'
             ORDER BY u.first_name ASC, u.last_name ASC'
        );
        $stmt->execute([
            'cohort_id' => $cohortId,
            'module_id' => $moduleId,
        ]);

        return $stmt->fetchAll();
    }

    /**
     * @return array<int, ReadinessTicket>
     */
    public function listByCohortAndModule(int $cohortId, int $moduleId): array
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM readiness_tickets
             WHERE cohort_id = :cohort_id AND module_id = :module_id
             ORDER BY user_id ASC'
        );
        $stmt->execute([
            'cohort_id' => $cohortId,
            'module_id' => $moduleId,
        ]);

        return array_map(
            static fn (array $row): ReadinessTicket => ReadinessTicket::fromArray($row),
            $stmt->fetchAll()
        );
    }
}
