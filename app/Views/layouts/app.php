<?php
$showAuthLinks = $showAuthLinks ?? false;
$showSidebar = $showSidebar ?? false;
$currentNav = $currentNav ?? '';
$isAdmin = $isAdmin ?? false;
$roles = $roles ?? [];
$user = $user ?? null;
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
</head>
<body class="flex flex-col min-h-[100dvh] md:h-full md:overflow-hidden font-sans bg-slate-50 text-black dark:bg-slate-950 dark:text-white">

    <header class="flex items-center justify-between gap-2 px-3 sm:px-4 md:px-6 py-3 md:py-4 border-b shrink-0 bg-white dark:bg-slate-900 border-slate-200 dark:border-slate-800 z-50">
        <div class="flex items-center gap-2 sm:gap-3 min-w-0">
            <?php if ($showSidebar): ?>
                <button
                    type="button"
                    id="sidebar-open-btn"
                    class="md:hidden flex items-center justify-center w-10 h-10 rounded-xl border border-slate-200 dark:border-slate-700 hover:bg-slate-100 dark:hover:bg-slate-800 shrink-0"
                    aria-label="<?= escape(__('nav.open_menu')) ?>"
                >
                    <i class="fa-solid fa-bars"></i>
                </button>
            <?php endif; ?>

            <a href="<?= escape(url($showSidebar ? '/dashboard' : '/')) ?>" class="flex items-center gap-2 sm:gap-3 min-w-0">
                <div class="flex items-center justify-center w-9 h-9 sm:w-10 sm:h-10 rounded-xl bg-gradient-to-br from-brand-500 to-brand-accent shadow-lg shadow-brand-500/20 shrink-0">
                    <i class="fa-solid fa-dumbbell text-slate-950 text-base sm:text-lg"></i>
                </div>
                <div class="min-w-0">
                    <span class="text-lg sm:text-xl font-bold tracking-wider text-black dark:text-white truncate block">
                        FIT<span class="text-brand-500">-FLIPPED</span>
                    </span>
                    <span class="hidden sm:inline-block px-2 py-0.5 text-[10px] uppercase tracking-widest font-semibold rounded bg-brand-50 dark:bg-brand-dark text-brand-600 dark:text-brand-accent border border-brand-500/30">
                        <?= escape(__('header.platform_badge')) ?>
                    </span>
                </div>
            </a>
        </div>

        <?php if ($showSidebar): ?>
            <div class="hidden xl:flex flex-col items-end text-xs text-slate-500 dark:text-slate-400 border-r pr-4 border-slate-200 dark:border-slate-800 shrink-0 max-w-xs">
                <span class="font-medium text-right"><?= escape(__('header.thesis_line1')) ?></span>
                <span class="text-right"><?= escape(__('header.thesis_line2')) ?></span>
            </div>
        <?php endif; ?>

        <div class="flex items-center gap-1.5 sm:gap-3 md:gap-4 shrink-0">
            <?php require base_path('app/Views/partials/header-controls.php'); ?>

            <?php if ($showSidebar && $user): ?>
                <?php
                $roleLabel = $roles !== []
                    ? translate_roles($roles)
                    : __('header.learner_label');
                ?>
                <div class="text-right hidden lg:block">
                    <p class="text-xs text-slate-500 dark:text-slate-400"><?= escape($roleLabel) ?></p>
                    <p class="text-sm font-semibold text-slate-700 dark:text-slate-200 max-w-[10rem] truncate"><?= escape($user->firstName . ' ' . $user->lastName) ?></p>
                </div>
                <?php require base_path('app/Views/partials/header-user-menu.php'); ?>
                <form method="POST" action="<?= escape(url('/logout')) ?>" class="hidden md:block">
                    <input type="hidden" name="csrf_token" value="<?= escape(csrf_token()) ?>">
                    <button type="submit" class="px-3 py-1.5 text-xs rounded-xl border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition whitespace-nowrap">
                        <?= escape(__('nav.sign_out')) ?>
                    </button>
                </form>
            <?php elseif ($showAuthLinks): ?>
                <a href="<?= escape(url('/login')) ?>" class="text-xs sm:text-sm text-slate-600 dark:text-slate-300 hover:text-brand-600 dark:hover:text-brand-accent whitespace-nowrap"><?= escape(__('nav.sign_in')) ?></a>
                <a href="<?= escape(url('/register')) ?>" class="px-3 sm:px-4 py-2 bg-brand-500 text-slate-950 font-semibold rounded-xl hover:bg-brand-accent transition duration-200 text-xs sm:text-sm shadow-lg shadow-brand-500/20 whitespace-nowrap">
                    <?= escape(__('nav.register')) ?>
                </a>
            <?php endif; ?>
        </div>
    </header>

    <?php if ($showSidebar): ?>
        <div class="flex flex-1 min-h-0 overflow-hidden relative">
            <?php require base_path('app/Views/partials/sidebar.php'); ?>
            <main class="flex-1 flex flex-col overflow-y-auto overflow-x-hidden bg-slate-50 dark:bg-slate-950 p-3 sm:p-4 md:p-8 pb-[calc(4.5rem+env(safe-area-inset-bottom))] md:pb-8">
                <?= $content ?? '' ?>
            </main>
        </div>
        <?php require base_path('app/Views/partials/mobile-bottom-nav.php'); ?>
    <?php else: ?>
        <main class="flex-1 overflow-y-auto overflow-x-hidden">
            <?= $content ?? '' ?>
        </main>
        <footer class="border-t border-slate-200 dark:border-slate-800 py-4 px-4 text-center text-xs text-slate-500 dark:text-slate-400 shrink-0">
            &copy; <?= date('Y') ?> <?= escape(__('app.name')) ?> — <?= escape(__('app.tagline')) ?>
        </footer>
    <?php endif; ?>

    <script src="<?= escape(url('/assets/theme.js')) ?>"></script>
    <script src="<?= escape(url('/assets/form-progress.js')) ?>"></script>
    <script src="<?= escape(url('/assets/password-toggle.js')) ?>"></script>
    <?php if ($showSidebar): ?>
        <script src="<?= escape(url('/assets/app.js')) ?>"></script>
        <?php if ($user): ?>
            <script src="<?= escape(url('/assets/user-menu.js')) ?>"></script>
        <?php endif; ?>
    <?php endif; ?>
</body>
</html>
