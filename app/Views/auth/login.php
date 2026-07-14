<?php
$email = $email ?? ($form['email'] ?? '');
$error = $error ?? null;
$errors = $errors ?? [];

ob_start();
?>
<div class="flex items-center justify-center px-6 py-16">
    <div class="w-full max-w-md">
        <div class="bg-white/80 backdrop-blur-md p-8 rounded-2xl border border-slate-200 shadow-sm">
            <h1 class="text-2xl font-bold text-slate-900 mb-2"><?= escape(__('auth.login_title')) ?></h1>
            <p class="text-slate-500 mb-6"><?= escape(__('auth.login_subtitle')) ?></p>

            <?php if ($error): ?>
                <div class="mb-4 p-4 rounded-xl bg-red-50 border border-red-200 text-red-700 text-sm">
                    <?= escape($error) ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="<?= escape(url('/login')) ?>" class="space-y-4">
                <input type="hidden" name="csrf_token" value="<?= escape(csrf_token()) ?>">

                <div>
                    <label for="email" class="block text-sm font-medium text-slate-700 mb-1"><?= escape(__('auth.email')) ?></label>
                    <input
                        type="email"
                        id="email"
                        name="email"
                        value="<?= escape($email) ?>"
                        required
                        class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500"
                    >
                    <?php if (!empty($errors['email'])): ?>
                        <p class="mt-1 text-sm text-red-600"><?= escape($errors['email'][0]) ?></p>
                    <?php endif; ?>
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium text-slate-700 mb-1"><?= escape(__('auth.password')) ?></label>
                    <input
                        type="password"
                        id="password"
                        name="password"
                        required
                        class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500"
                    >
                    <?php if (!empty($errors['password'])): ?>
                        <p class="mt-1 text-sm text-red-600"><?= escape($errors['password'][0]) ?></p>
                    <?php endif; ?>
                </div>

                <button
                    type="submit"
                    class="w-full py-3 bg-indigo-600 text-white font-semibold rounded-xl hover:bg-indigo-700 transition-colors"
                >
                    <?= escape(__('auth.sign_in')) ?>
                </button>
            </form>

            <p class="mt-6 text-center text-sm text-slate-500">
                <?= escape(__('auth.no_account')) ?>
                <a href="<?= escape(url('/register')) ?>" class="text-indigo-600 hover:text-indigo-700 font-medium"><?= escape(__('auth.create_one')) ?></a>
            </p>
        </div>
    </div>
</div>
<?php
$content = ob_get_clean();
$showAuthLinks = true;
require base_path('app/Views/layouts/app.php');
