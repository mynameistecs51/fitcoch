<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= escape($title ?? 'FMMP') ?> — FitCoch</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-slate-50 text-slate-900">
    <div class="min-h-screen flex flex-col">
        <header class="border-b border-slate-200 bg-white/90 backdrop-blur-md">
            <div class="max-w-5xl mx-auto px-6 py-4 flex items-center justify-between">
                <a href="/" class="text-xl font-bold text-indigo-600">FitCoch</a>
                <nav class="flex items-center gap-4 text-sm">
                    <?php if (!empty($showAuthLinks)): ?>
                        <a href="/login" class="text-slate-600 hover:text-indigo-600">Sign In</a>
                        <a href="/register" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors">Register</a>
                    <?php endif; ?>
                </nav>
            </div>
        </header>

        <main class="flex-1">
            <?= $content ?? '' ?>
        </main>

        <footer class="border-t border-slate-200 py-6 text-center text-sm text-slate-500">
            &copy; <?= date('Y') ?> FitCoch — Flipped-Microlearning MOOC Platform
        </footer>
    </div>
</body>
</html>
