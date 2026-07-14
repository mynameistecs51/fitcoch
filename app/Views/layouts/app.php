<!DOCTYPE html>
<html lang="<?= escape(locale()) ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= escape($title ?? __('app.name')) ?> — <?= escape(__('app.name')) ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Noto+Sans+Thai:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { font-family: 'Inter', 'Noto Sans Thai', sans-serif; }
    </style>
</head>
<body class="min-h-screen bg-slate-50 text-slate-900">
    <div class="min-h-screen flex flex-col">
        <header class="border-b border-slate-200 bg-white/90 backdrop-blur-md">
            <div class="max-w-5xl mx-auto px-6 py-4 flex items-center justify-between">
                <a href="<?= escape(url('/')) ?>" class="text-xl font-bold text-indigo-600"><?= escape(__('app.name')) ?></a>
                <nav class="flex items-center gap-4 text-sm">
                    <div class="flex items-center gap-1 rounded-lg border border-slate-200 p-1">
                        <a
                            href="<?= escape(url('/lang/en')) ?>"
                            class="px-2 py-1 rounded-md <?= locale() === 'en' ? 'bg-indigo-600 text-white' : 'text-slate-600 hover:text-indigo-600' ?>"
                        ><?= escape(__('lang.english')) ?></a>
                        <a
                            href="<?= escape(url('/lang/th')) ?>"
                            class="px-2 py-1 rounded-md <?= locale() === 'th' ? 'bg-indigo-600 text-white' : 'text-slate-600 hover:text-indigo-600' ?>"
                        ><?= escape(__('lang.thai')) ?></a>
                    </div>
                    <?php if (!empty($showAuthLinks)): ?>
                        <a href="<?= escape(url('/login')) ?>" class="text-slate-600 hover:text-indigo-600"><?= escape(__('nav.sign_in')) ?></a>
                        <a href="<?= escape(url('/register')) ?>" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors"><?= escape(__('nav.register')) ?></a>
                    <?php endif; ?>
                </nav>
            </div>
        </header>

        <main class="flex-1">
            <?= $content ?? '' ?>
        </main>

        <footer class="border-t border-slate-200 py-6 text-center text-sm text-slate-500">
            &copy; <?= date('Y') ?> <?= escape(__('app.name')) ?> — <?= escape(__('app.tagline')) ?>
        </footer>
    </div>
</body>
</html>
