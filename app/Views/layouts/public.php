<?php
$currentNav = $currentNav ?? 'home';
?>
<!DOCTYPE html>
<html lang="<?= escape(locale()) ?>" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title><?= escape($title ?? __('app.name')) ?> — <?= escape(__('app.name')) ?></title>
    <script>
        (function () {
            const t = localStorage.getItem('fitcoch-theme') || 'light';
            document.documentElement.classList.toggle('dark', t === 'dark');
        })();
    </script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600;700&family=Sarabun:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Prompt', 'Sarabun', 'sans-serif'],
                    },
                    colors: {
                        brand: {
                            50: '#f0fdf4',
                            100: '#dcfce7',
                            500: '#22c55e',
                            600: '#16a34a',
                            accent: '#a3e635',
                            dark: '#064e3b',
                        },
                    },
                },
            },
        };
    </script>
    <link rel="stylesheet" href="<?= escape(url('/assets/app.css')) ?>">
    <link rel="stylesheet" href="<?= escape(url('/assets/landing.css')) ?>">
</head>
<body class="min-h-[100dvh] font-sans bg-slate-50 text-black dark:bg-slate-950 dark:text-white flex flex-col">

    <header class="landing-header sticky top-0 z-50 border-b border-slate-200/80 dark:border-slate-800/80 bg-white/90 dark:bg-slate-950/90 backdrop-blur-md">
        <div class="max-w-6xl mx-auto px-4 sm:px-6">
            <div class="flex items-center justify-between h-16 md:h-[4.5rem] gap-3">
                <a href="<?= escape(url('/')) ?>" class="flex items-center gap-2.5 min-w-0 shrink-0">
                    <div class="flex items-center justify-center w-10 h-10 rounded-xl bg-gradient-to-br from-sky-500 to-brand-500 shadow-lg shadow-sky-500/20">
                        <i class="fa-solid fa-dumbbell text-white text-lg"></i>
                    </div>
                    <div class="min-w-0 hidden sm:block">
                        <span class="text-lg font-bold tracking-wide text-slate-900 dark:text-white block leading-tight">
                            FIT<span class="text-sky-500">-FLIPPED</span>
                        </span>
                        <span class="text-[10px] uppercase tracking-widest text-slate-500 dark:text-slate-400 font-semibold">
                            <?= escape(__('header.platform_badge')) ?>
                        </span>
                    </div>
                </a>

                <nav class="hidden lg:flex items-center gap-1 text-sm font-medium">
                    <?php
                    $navItems = [
                        ['id' => 'home', 'href' => url('/'), 'label' => __('home.nav.home')],
                        ['id' => 'courses', 'href' => url('/#all-courses'), 'label' => __('home.nav.courses')],
                        ['id' => 'categories', 'href' => url('/#categories'), 'label' => __('home.nav.categories')],
                        ['id' => 'features', 'href' => url('/#features'), 'label' => __('home.nav.features')],
                    ];
                    foreach ($navItems as $item):
                    ?>
                        <a
                            href="<?= escape($item['href']) ?>"
                            class="px-3 py-2 rounded-xl transition <?= $currentNav === $item['id'] ? 'text-sky-600 dark:text-sky-400 bg-sky-500/10' : 'text-slate-600 dark:text-slate-300 hover:text-sky-600 dark:hover:text-sky-400 hover:bg-slate-100 dark:hover:bg-slate-800' ?>"
                        ><?= escape($item['label']) ?></a>
                    <?php endforeach; ?>
                </nav>

                <div class="flex items-center gap-2 sm:gap-3 shrink-0">
                    <?php require base_path('app/Views/partials/header-controls.php'); ?>
                    <a href="<?= escape(url('/login')) ?>" class="hidden sm:inline text-sm text-slate-600 dark:text-slate-300 hover:text-sky-600 dark:hover:text-sky-400 font-medium whitespace-nowrap">
                        <?= escape(__('nav.sign_in')) ?>
                    </a>
                    <a href="<?= escape(url('/login')) ?>" class="sm:hidden flex items-center justify-center w-10 h-10 rounded-xl border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300" aria-label="<?= escape(__('home.nav.classroom')) ?>">
                        <i class="fa-solid fa-right-to-bracket"></i>
                    </a>
                    <a href="<?= escape(url('/login')) ?>" class="px-3 sm:px-5 py-2 sm:py-2.5 bg-gradient-to-r from-sky-500 to-brand-500 text-white font-bold rounded-xl hover:opacity-90 transition text-xs sm:text-sm shadow-lg shadow-sky-500/25 whitespace-nowrap">
                        <?= escape(__('home.nav.classroom')) ?>
                    </a>
                </div>
            </div>
        </div>
    </header>

    <main class="flex-1">
        <?= $content ?? '' ?>
    </main>

    <footer class="landing-footer border-t border-slate-200 dark:border-slate-800 bg-slate-900 text-slate-300">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 py-12">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-10">
                <div>
                    <div class="flex items-center gap-2 mb-4">
                        <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-sky-500 to-brand-500 flex items-center justify-center">
                            <i class="fa-solid fa-dumbbell text-white"></i>
                        </div>
                        <span class="text-xl font-bold text-white">FIT<span class="text-sky-400">-FLIPPED</span></span>
                    </div>
                    <p class="text-sm text-slate-400 leading-relaxed"><?= escape(__('home.footer.tagline')) ?></p>
                </div>
                <div>
                    <h3 class="text-sm font-bold text-white uppercase tracking-wider mb-4"><?= escape(__('home.footer.menu')) ?></h3>
                    <ul class="space-y-2 text-sm">
                        <li><a href="<?= escape(url('/#all-courses')) ?>" class="hover:text-sky-400 transition"><?= escape(__('home.nav.courses')) ?></a></li>
                        <li><a href="<?= escape(url('/#categories')) ?>" class="hover:text-sky-400 transition"><?= escape(__('home.nav.categories')) ?></a></li>
                        <li><a href="<?= escape(url('/#features')) ?>" class="hover:text-sky-400 transition"><?= escape(__('home.nav.features')) ?></a></li>
                        <li><a href="<?= escape(url('/login')) ?>" class="hover:text-sky-400 transition"><?= escape(__('nav.sign_in')) ?></a></li>
                        <li><a href="<?= escape(url('/register')) ?>" class="hover:text-sky-400 transition"><?= escape(__('nav.register')) ?></a></li>
                    </ul>
                </div>
                <div>
                    <h3 class="text-sm font-bold text-white uppercase tracking-wider mb-4"><?= escape(__('home.footer.about')) ?></h3>
                    <p class="text-sm text-slate-400 leading-relaxed mb-4"><?= escape(__('home.footer.about_text')) ?></p>
                    <p class="text-xs text-slate-500"><?= escape(__('header.thesis_line1')) ?></p>
                    <p class="text-xs text-slate-500"><?= escape(__('header.thesis_line2')) ?></p>
                </div>
            </div>
            <div class="border-t border-slate-800 mt-10 pt-6 text-center text-xs text-slate-500">
                &copy; <?= date('Y') ?> <?= escape(__('app.name')) ?> — <?= escape(__('app.tagline')) ?>
            </div>
        </div>
    </footer>

    <script src="<?= escape(url('/assets/theme.js')) ?>"></script>
    <script src="<?= escape(url('/assets/landing.js')) ?>"></script>
</body>
</html>
