<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Course;
use App\Models\KnowledgeItem;
use App\Repositories\CourseRepository;
use App\Repositories\KnowledgeItemRepository;
use App\Repositories\ModuleRepository;
use Exception;

class KnowledgeItemService
{
    public function __construct(
        private readonly CourseRepository $courseRepo,
        private readonly ModuleRepository $moduleRepo,
        private readonly KnowledgeItemRepository $itemRepo,
    ) {
    }

    /**
     * @return array{course: Course, items: array<int, KnowledgeItem>}|null
     */
    public function getCoursePanel(int $courseId): ?array
    {
        $course = $this->courseRepo->findById($courseId);

        if ($course === null) {
            return null;
        }

        return [
            'course' => $course,
            'items' => $this->itemRepo->listByCourseId($courseId),
        ];
    }

    /**
     * @param array<string, mixed> $data
     */
    public function createItem(int $courseId, array $data): KnowledgeItem
    {
        $course = $this->courseRepo->findById($courseId);

        if ($course === null) {
            throw new Exception(__('courses.validation.not_found'));
        }

        $validated = $this->validateItemData($data);

        return $this->itemRepo->create([
            'course_id' => $courseId,
            'concept_name' => $validated['concept_name'],
            'description' => $validated['description'],
        ]);
    }

    /**
     * @param array<string, mixed> $data
     */
    public function updateItem(int $courseId, int $itemId, array $data): KnowledgeItem
    {
        $item = $this->requireItemForCourse($courseId, $itemId);
        $validated = $this->validateItemData($data);

        return $this->itemRepo->update($item->id, $validated);
    }

    public function deleteItem(int $courseId, int $itemId): void
    {
        $item = $this->requireItemForCourse($courseId, $itemId);
        $this->itemRepo->delete($item->id);
    }

    public function syncFromModules(int $courseId): int
    {
        $course = $this->courseRepo->findById($courseId);

        if ($course === null) {
            throw new Exception(__('courses.validation.not_found'));
        }

        $created = 0;

        foreach ($this->moduleRepo->listByCourseId($courseId) as $module) {
            if ($this->itemRepo->existsByCourseAndConcept($courseId, $module->title)) {
                continue;
            }

            $this->itemRepo->create([
                'course_id' => $courseId,
                'concept_name' => $module->title,
                'description' => __('knowledge_items.default_description', ['module' => $module->title]),
            ]);
            $created++;
        }

        return $created;
    }

    private function requireItemForCourse(int $courseId, int $itemId): KnowledgeItem
    {
        $item = $this->itemRepo->findById($itemId);

        if ($item === null || $item->courseId !== $courseId) {
            throw new Exception(__('knowledge_items.validation.not_found'));
        }

        return $item;
    }

    /**
     * @param array<string, mixed> $data
     * @return array{concept_name: string, description: ?string}
     */
    private function validateItemData(array $data): array
    {
        $errors = [];
        $conceptName = trim((string) ($data['concept_name'] ?? ''));
        $description = trim((string) ($data['description'] ?? ''));

        if ($conceptName === '') {
            $errors['concept_name'][] = __('knowledge_items.validation.concept_required');
        } elseif (mb_strlen($conceptName) > 100) {
            $errors['concept_name'][] = __('knowledge_items.validation.concept_max');
        }

        if ($description !== '' && mb_strlen($description) > 5000) {
            $errors['description'][] = __('knowledge_items.validation.description_max');
        }

        if ($errors !== []) {
            throw new ValidationException(__('errors.validation_failed'), $errors);
        }

        return [
            'concept_name' => $conceptName,
            'description' => $description !== '' ? $description : null,
        ];
    }
}
