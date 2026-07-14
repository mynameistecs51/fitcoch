<?php
$showAuthLinks = $showAuthLinks ?? false;
$showSidebar = $showSidebar ?? false;
$currentNav = $currentNav ?? '';
$isAdmin = $isAdmin ?? false;
$user = $user ?? null;
?>
<!DOCTYPE html>
<html lang="<?= escape(locale()) ?>" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= escape($title ?? __('app.name')) ?> — <?= escape(__('app.name')) ?></title>
    <script>
        (function () {
            const t = localStorage.getItem('fitcoch-theme');
            document.documentElement.classList.toggle('dark', t !== 'light');
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
<body class="flex flex-col h-full overflow-hidden font-sans bg-slate-50 text-slate-900 dark:bg-slate-950 dark:text-slate-100">

    <header class="flex items-center justify-between px-4 md:px-6 py-4 border-b shrink-0 bg-white dark:bg-slate-900 border-slate-200 dark:border-slate-800">
        <div class="flex items-center space-x-3">
            <a href="<?= escape(url($showSidebar ? '/dashboard' : '/')) ?>" class="flex items-center space-x-3">
                <div class="flex items-center justify-center w-10 h-10 rounded-xl bg-gradient-to-br from-brand-500 to-brand-accent shadow-lg shadow-brand-500/20">
                    <i class="fa-solid fa-dumbbell text-slate-950 text-lg"></i>
                </div>
                <div>
                    <span class="text-xl font-bold tracking-wider text-slate-900 dark:text-white">
                        FIT<span class="text-brand-500">-FLIPPED</span>
                    </span>
                    <span class="hidden md:inline-block px-2 py-0.5 ml-2 text-[10px] uppercase tracking-widest font-semibold rounded bg-brand-50 dark:bg-brand-dark text-brand-600 dark:text-brand-accent border border-brand-500/30">
                        <?= escape(__('header.platform_badge')) ?>
                    </span>
                </div>
            </a>
        </div>

        <?php if ($showSidebar): ?>
            <div class="hidden lg:flex flex-col items-end text-xs text-slate-500 dark:text-slate-400 border-r pr-4 border-slate-200 dark:border-slate-800">
                <span class="font-medium"><?= escape(__('header.thesis_line1')) ?></span>
                <span><?= escape(__('header.thesis_line2')) ?></span>
            </div>
        <?php endif; ?>

        <div class="flex items-center space-x-3 md:space-x-4">
            <?php require base_path('app/Views/partials/header-controls.php'); ?>

            <?php if ($showSidebar && $user): ?>
                <div class="text-right hidden sm:block">
                    <p class="text-xs text-slate-500 dark:text-slate-400"><?= escape(__('header.learner_label')) ?></p>
                    <p class="text-sm font-semibold text-slate-700 dark:text-slate-200"><?= escape($user->firstName . ' ' . $user->lastName) ?></p>
                </div>
                <div class="relative w-10 h-10 overflow-hidden rounded-full border-2 border-brand-500/50 bg-slate-100 dark:bg-slate-800 flex items-center justify-center">
                    <i class="fa-solid fa-user-tie text-brand-600 dark:text-brand-accent"></i>
                </div>
                <form method="POST" action="<?= escape(url('/logout')) ?>" class="hidden md:block">
                    <input type="hidden" name="csrf_token" value="<?= escape(csrf_token()) ?>">
                    <button type="submit" class="px-3 py-1.5 text-xs rounded-xl border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition">
                        <?= escape(__('nav.sign_out')) ?>
                    </button>
                </form>
            <?php elseif ($showAuthLinks): ?>
                <a href="<?= escape(url('/login')) ?>" class="text-sm text-slate-600 dark:text-slate-300 hover:text-brand-600 dark:hover:text-brand-accent"><?= escape(__('nav.sign_in')) ?></a>
                <a href="<?= escape(url('/register')) ?>" class="px-4 py-2 bg-brand-500 text-slate-950 font-semibold rounded-xl hover:bg-brand-accent transition duration-200 text-sm shadow-lg shadow-brand-500/20">
                    <?= escape(__('nav.register')) ?>
                </a>
            <?php endif; ?>
        </div>
    </header>

    <?php if ($showSidebar): ?>
        <div class="flex flex-1 overflow-hidden">
            <?php require base_path('app/Views/partials/sidebar.php'); ?>
            <main class="flex-1 flex flex-col overflow-y-auto bg-slate-50 dark:bg-slate-950 p-4 md:p-8">
                <?= $content ?? '' ?>
            </main>
        </div>
    <?php else: ?>
        <main class="flex-1 overflow-y-auto">
            <?= $content ?? '' ?>
        </main>
        <footer class="border-t border-slate-200 dark:border-slate-800 py-4 text-center text-xs text-slate-500 dark:text-slate-400 shrink-0">
            &copy; <?= date('Y') ?> <?= escape(__('app.name')) ?> — <?= escape(__('app.tagline')) ?>
        </footer>
    <?php endif; ?>

    <script src="<?= escape(url('/assets/theme.js')) ?>"></script>
</body>
</html>
