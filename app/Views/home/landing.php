<?php
$landing = $landing ?? [
    'stats' => ['courses' => 0, 'learners' => 0, 'certificates' => 0, 'modules' => 0],
    'courses' => [],
    'newest_courses' => [],
    'featured_courses' => [],
    'categories' => [],
    'search_query' => '',
];
$stats = $landing['stats'];
$searchQuery = $landing['search_query'];
$categories = $landing['categories'];
$newestCourses = $landing['newest_courses'];
$featuredCourses = $landing['featured_courses'];
$allCourses = $landing['courses'];
$user = $user ?? null;

ob_start();
?>
<div class="landing-page">
    <section class="landing-hero relative overflow-hidden">
        <div class="landing-hero-bg absolute inset-0"></div>
        <div class="landing-hero-pattern absolute inset-0 opacity-30"></div>
        <div class="relative z-10 max-w-6xl mx-auto px-4 sm:px-6 py-16 md:py-24 text-center">
            <p class="landing-hero-badge inline-flex items-center gap-2 px-4 py-1.5 rounded-full text-xs font-semibold tracking-widest uppercase mb-6">
                <i class="fa-solid fa-graduation-cap"></i>
                <?= escape(__('home.hero_badge')) ?>
            </p>
            <h1 class="text-3xl sm:text-4xl md:text-6xl font-extrabold text-white leading-tight mb-4">
                <?= escape(__('home.hero_title_line1')) ?><br>
                <span class="landing-hero-accent"><?= escape(__('home.hero_title_line2')) ?></span>
            </h1>
            <p class="text-sm sm:text-base md:text-lg text-sky-100/90 max-w-2xl mx-auto mb-8 leading-relaxed">
                <?= escape(__('home.hero_subtitle')) ?>
            </p>

            <form method="GET" action="<?= escape(url('/')) ?>" class="landing-search max-w-2xl mx-auto" role="search">
                <div class="flex flex-col sm:flex-row gap-2 sm:gap-0">
                    <label for="landing-search" class="sr-only"><?= escape(__('home.search_label')) ?></label>
                    <input
                        type="search"
                        id="landing-search"
                        name="q"
                        value="<?= escape($searchQuery) ?>"
                        placeholder="<?= escape(__('home.search_placeholder')) ?>"
                        class="flex-1 px-5 py-4 rounded-2xl sm:rounded-r-none sm:rounded-l-2xl border-0 text-slate-900 placeholder:text-slate-400 focus:outline-none focus:ring-4 focus:ring-brand-500/30 text-sm sm:text-base"
                    >
                    <button
                        type="submit"
                        class="px-6 py-4 bg-brand-500 hover:bg-brand-accent text-slate-950 font-bold rounded-2xl sm:rounded-l-none sm:rounded-r-2xl transition shadow-lg shadow-brand-500/30 text-sm sm:text-base"
                    >
                        <i class="fa-solid fa-magnifying-glass mr-2"></i><?= escape(__('home.search_button')) ?>
                    </button>
                </div>
            </form>
        </div>
    </section>

    <section class="landing-stats border-b border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 py-10">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-6 md:gap-8">
                <?php
                $statItems = [
                    ['key' => 'courses', 'icon' => 'fa-book-open', 'label' => __('home.stats.courses')],
                    ['key' => 'learners', 'icon' => 'fa-users', 'label' => __('home.stats.learners')],
                    ['key' => 'certificates', 'icon' => 'fa-certificate', 'label' => __('home.stats.certificates')],
                    ['key' => 'modules', 'icon' => 'fa-layer-group', 'label' => __('home.stats.modules')],
                ];
                foreach ($statItems as $item):
                ?>
                    <div class="landing-stat text-center">
                        <div class="inline-flex items-center justify-center w-12 h-12 rounded-2xl bg-sky-500/10 text-sky-600 dark:text-sky-400 mb-3">
                            <i class="fa-solid <?= escape($item['icon']) ?> text-lg"></i>
                        </div>
                        <p class="text-3xl md:text-4xl font-extrabold text-slate-900 dark:text-white landing-stat-value" data-target="<?= escape((string) ($stats[$item['key']] ?? 0)) ?>">0</p>
                        <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-1"><?= escape($item['label']) ?></p>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <section class="py-12 md:py-16 bg-slate-50 dark:bg-slate-950" id="categories">
        <div class="max-w-6xl mx-auto px-4 sm:px-6">
            <h2 class="text-2xl md:text-3xl font-bold text-slate-900 dark:text-white mb-2"><?= escape(__('home.categories_title')) ?></h2>
            <p class="text-sm text-slate-500 dark:text-slate-400 mb-8"><?= escape(__('home.categories_subtitle')) ?></p>
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3">
                <?php foreach ($categories as $category): ?>
                    <button
                        type="button"
                        class="landing-category-pill group flex items-center gap-3 p-4 rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 hover:border-sky-500/50 hover:shadow-lg hover:shadow-sky-500/10 transition text-left"
                        data-category-keywords="<?= escape($category['keywords']) ?>"
                    >
                        <span class="flex items-center justify-center w-10 h-10 rounded-xl bg-gradient-to-br from-sky-500/20 to-brand-500/20 text-sky-600 dark:text-sky-400 group-hover:scale-110 transition">
                            <i class="fa-solid <?= escape($category['icon']) ?>"></i>
                        </span>
                        <span class="text-sm font-semibold text-slate-800 dark:text-slate-200 leading-snug"><?= escape($category['label']) ?></span>
                    </button>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <?php
    $sections = [
        ['id' => 'new-courses', 'title' => __('home.new_courses'), 'courses' => $newestCourses, 'icon' => 'fa-sparkles'],
        ['id' => 'featured-courses', 'title' => __('home.featured_courses'), 'courses' => $featuredCourses, 'icon' => 'fa-star'],
    ];
    foreach ($sections as $section):
        if ($section['courses'] === []) {
            continue;
        }
    ?>
        <section class="py-12 md:py-16 bg-white dark:bg-slate-900 border-t border-slate-100 dark:border-slate-800" id="<?= escape($section['id']) ?>">
            <div class="max-w-6xl mx-auto px-4 sm:px-6">
                <div class="flex items-center justify-between gap-4 mb-8">
                    <h2 class="text-2xl md:text-3xl font-bold text-slate-900 dark:text-white flex items-center gap-3">
                        <i class="fa-solid <?= escape($section['icon']) ?> text-sky-500"></i>
                        <?= escape($section['title']) ?>
                    </h2>
                    <a href="#all-courses" class="text-sm font-semibold text-sky-600 dark:text-sky-400 hover:text-brand-500 transition whitespace-nowrap">
                        <?= escape(__('home.view_all')) ?> <i class="fa-solid fa-arrow-right ml-1"></i>
                    </a>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
                    <?php foreach ($section['courses'] as $course): ?>
                        <?php require base_path('app/Views/partials/landing-course-card.php'); ?>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>
    <?php endforeach; ?>

    <section class="py-12 md:py-16 bg-slate-50 dark:bg-slate-950" id="all-courses">
        <div class="max-w-6xl mx-auto px-4 sm:px-6">
            <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4 mb-8">
                <div>
                    <h2 class="text-2xl md:text-3xl font-bold text-slate-900 dark:text-white"><?= escape(__('home.all_courses')) ?></h2>
                    <p class="text-sm text-slate-500 dark:text-slate-400 mt-1"><?= escape(__('home.all_courses_subtitle')) ?></p>
                </div>
                <?php if ($searchQuery !== ''): ?>
                    <a href="<?= escape(url('/')) ?>" class="text-sm text-sky-600 dark:text-sky-400 hover:underline">
                        <?= escape(__('home.clear_search')) ?>
                    </a>
                <?php endif; ?>
            </div>

            <?php if ($allCourses === []): ?>
                <div class="rounded-3xl border border-dashed border-slate-300 dark:border-slate-700 p-12 text-center">
                    <i class="fa-solid fa-book-open text-4xl text-slate-300 dark:text-slate-600 mb-4"></i>
                    <p class="text-slate-600 dark:text-slate-400"><?= escape(__('home.no_courses')) ?></p>
                </div>
            <?php else: ?>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-5" id="landing-course-grid">
                    <?php foreach ($allCourses as $course): ?>
                        <?php require base_path('app/Views/partials/landing-course-card.php'); ?>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <section class="py-12 md:py-16 bg-white dark:bg-slate-900 border-t border-slate-100 dark:border-slate-800" id="features">
        <div class="max-w-6xl mx-auto px-4 sm:px-6">
            <h2 class="text-2xl md:text-3xl font-bold text-slate-900 dark:text-white mb-2"><?= escape(__('home.features_title')) ?></h2>
            <p class="text-sm text-slate-500 dark:text-slate-400 mb-10"><?= escape(__('home.features_subtitle')) ?></p>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <?php
                $features = [
                    ['icon' => 'fa-arrows-rotate', 'title' => __('home.features.flipped.title'), 'desc' => __('home.features.flipped.desc')],
                    ['icon' => 'fa-puzzle-piece', 'title' => __('home.features.micro.title'), 'desc' => __('home.features.micro.desc')],
                    ['icon' => 'fa-chart-line', 'title' => __('home.features.analytics.title'), 'desc' => __('home.features.analytics.desc')],
                ];
                foreach ($features as $feature):
                ?>
                    <div class="landing-feature-card rounded-3xl border border-slate-200 dark:border-slate-800 p-6 md:p-8">
                        <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-sky-500 to-brand-500 flex items-center justify-center text-white text-xl mb-5 shadow-lg shadow-sky-500/20">
                            <i class="fa-solid <?= escape($feature['icon']) ?>"></i>
                        </div>
                        <h3 class="text-lg font-bold text-slate-900 dark:text-white mb-2"><?= escape($feature['title']) ?></h3>
                        <p class="text-sm text-slate-600 dark:text-slate-400 leading-relaxed"><?= escape($feature['desc']) ?></p>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <section class="landing-cta relative overflow-hidden">
        <div class="landing-cta-bg absolute inset-0"></div>
        <div class="relative z-10 max-w-4xl mx-auto px-4 sm:px-6 py-16 md:py-20 text-center">
            <h2 class="text-2xl md:text-4xl font-extrabold text-white mb-4"><?= escape(__('home.cta_title')) ?></h2>
            <p class="text-sky-100/90 mb-8 text-sm md:text-base"><?= escape(__('home.cta_subtitle')) ?></p>
            <div class="flex flex-col sm:flex-row items-center justify-center gap-3">
                <a href="<?= escape(url('/register')) ?>" class="w-full sm:w-auto px-8 py-4 bg-brand-500 hover:bg-brand-accent text-slate-950 font-bold rounded-2xl transition shadow-lg shadow-brand-500/30">
                    <?= escape(__('home.cta_register')) ?>
                </a>
                <a href="<?= escape(url('/login')) ?>" class="w-full sm:w-auto px-8 py-4 bg-white/10 hover:bg-white/20 text-white font-semibold rounded-2xl border border-white/30 transition backdrop-blur">
                    <?= escape(__('home.cta_login')) ?>
                </a>
            </div>
        </div>
    </section>
</div>
<?php
$content = ob_get_clean();
$user = $user ?? null;
$roles = $roles ?? [];
require base_path('app/Views/layouts/public.php');
