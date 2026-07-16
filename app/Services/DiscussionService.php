<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Request;
use App\Models\DiscussionPost;
use App\Repositories\CohortRepository;
use App\Repositories\DiscussionRepository;
use App\Repositories\ModuleRepository;
use Exception;

class DiscussionService
{
    public function __construct(
        private readonly DiscussionRepository $discussionRepo,
        private readonly ModuleRepository $moduleRepo,
        private readonly CohortRepository $cohortRepo,
        private readonly AuthorizationService $authzService,
    ) {
    }

    /**
     * @return array{
     *     posts: array<int, DiscussionPost>,
     *     can_post: bool
     * }
     */
    public function getModulePanel(int $moduleId, int $userId): array
    {
        $module = $this->moduleRepo->findById($moduleId);

        if ($module === null || !$this->canAccessModule($userId, $module->courseId)) {
            return ['posts' => [], 'can_post' => false];
        }

        return [
            'posts' => $this->discussionRepo->listByModuleId($moduleId),
            'can_post' => $this->canPost($userId, $module->courseId),
        ];
    }

    /**
     * @param array<string, mixed> $data
     */
    public function createPost(int $moduleId, int $userId, array $data): DiscussionPost
    {
        $module = $this->moduleRepo->findById($moduleId);

        if ($module === null) {
            throw new Exception(__('discussion.validation.module_not_found'));
        }

        if (!$this->canPost($userId, $module->courseId)) {
            throw new Exception(__('discussion.validation.not_enrolled'));
        }

        $body = trim((string) ($data['body'] ?? ''));

        if ($body === '') {
            throw new ValidationException(__('errors.validation_failed'), [
                'body' => [__('discussion.validation.body_required')],
            ]);
        }

        if (mb_strlen($body) > 2000) {
            throw new ValidationException(__('errors.validation_failed'), [
                'body' => [__('discussion.validation.body_max')],
            ]);
        }

        return $this->discussionRepo->create([
            'module_id' => $moduleId,
            'user_id' => $userId,
            'body' => $body,
        ]);
    }

    /**
     * @return array{
     *     discussionModuleId: int,
     *     discussionPosts: array<int, \App\Models\DiscussionPost>,
     *     discussionCanPost: bool,
     *     discussionSuccess: ?string,
     *     discussionError: ?string,
     *     discussionRedirectUrl: string
     * }
     */
    public function buildViewContext(Request $request, int $moduleId, int $userId, string $redirectUrl): array
    {
        $panel = $this->getModulePanel($moduleId, $userId);
        $query = $request->query();
        $targetModule = (string) ($query['discussion_module'] ?? '');

        return [
            'discussionModuleId' => $moduleId,
            'discussionPosts' => $panel['posts'],
            'discussionCanPost' => $panel['can_post'],
            'discussionSuccess' => $targetModule === (string) $moduleId ? ($query['discussion_success'] ?? null) : null,
            'discussionError' => $targetModule === (string) $moduleId ? ($query['discussion_error'] ?? null) : null,
            'discussionRedirectUrl' => $redirectUrl,
        ];
    }

    private function canAccessModule(int $userId, int $courseId): bool
    {
        return $this->canPost($userId, $courseId);
    }

    private function canPost(int $userId, int $courseId): bool
    {
        if ($this->cohortRepo->findActiveEnrollmentForUser($userId, $courseId) !== null) {
            return true;
        }

        return $this->authzService->hasAnyRole($userId, ['instructor', 'admin']);
    }
}
