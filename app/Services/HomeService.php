<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\CertificateRepository;
use App\Repositories\CourseRepository;
use App\Repositories\ModuleRepository;
use App\Repositories\UserRepository;

class HomeService
{
    public function __construct(
        private readonly CourseRepository $courseRepo,
        private readonly UserRepository $userRepo,
        private readonly CertificateRepository $certificateRepo,
        private readonly ModuleRepository $moduleRepo,
    ) {
    }

    /** @return array<string, mixed> */
    public function buildLandingData(?string $searchQuery = null): array
    {
        $courses = $this->courseRepo->listPublished();
        $cards = [];

        foreach ($courses as $course) {
            $modules = $this->moduleRepo->listByCourseId($course->id);
            $cards[] = [
                'id' => $course->id,
                'title' => $course->title,
                'description' => $course->description ?? '',
                'module_count' => count($modules),
                'created_at' => $course->createdAt,
            ];
        }

        if ($searchQuery !== null && $searchQuery !== '') {
            $needle = mb_strtolower($searchQuery);
            $cards = array_values(array_filter(
                $cards,
                static function (array $card) use ($needle): bool {
                    $haystack = mb_strtolower($card['title'] . ' ' . $card['description']);

                    return str_contains($haystack, $needle);
                }
            ));
        }

        $newest = $cards;
        usort(
            $newest,
            static fn (array $a, array $b): int => strcmp($b['created_at'], $a['created_at'])
        );
        $newest = array_slice($newest, 0, 4);

        $featured = array_slice($cards, 0, 4);

        return [
            'stats' => [
                'courses' => count($courses),
                'learners' => $this->userRepo->countActive(),
                'certificates' => $this->certificateRepo->countAll(),
                'modules' => $this->moduleRepo->countPublishedModules(),
            ],
            'courses' => $cards,
            'newest_courses' => $newest,
            'featured_courses' => $featured,
            'categories' => $this->categories(),
            'search_query' => $searchQuery ?? '',
        ];
    }

    /** @return array<int, array{id: string, label: string, icon: string, keywords: string}> */
    private function categories(): array
    {
        return [
            ['id' => 'physiology', 'label' => __('home.categories.physiology'), 'icon' => 'fa-heart-pulse', 'keywords' => 'physiology สรีรวิทยา'],
            ['id' => 'biomechanics', 'label' => __('home.categories.biomechanics'), 'icon' => 'fa-person-running', 'keywords' => 'biomechanics ชีวกลศาสตร์'],
            ['id' => 'training', 'label' => __('home.categories.training'), 'icon' => 'fa-dumbbell', 'keywords' => 'training ฝึก'],
            ['id' => 'nutrition', 'label' => __('home.categories.nutrition'), 'icon' => 'fa-apple-whole', 'keywords' => 'nutrition โภชนาการ'],
            ['id' => 'psychology', 'label' => __('home.categories.psychology'), 'icon' => 'fa-brain', 'keywords' => 'psychology จิตวิทยา'],
            ['id' => 'research', 'label' => __('home.categories.research'), 'icon' => 'fa-flask', 'keywords' => 'research วิจัย'],
            ['id' => 'coaching', 'label' => __('home.categories.coaching'), 'icon' => 'fa-person-chalkboard', 'keywords' => 'coaching โค้ช'],
            ['id' => 'health', 'label' => __('home.categories.health'), 'icon' => 'fa-notes-medical', 'keywords' => 'health สุขภาพ'],
        ];
    }

}
