<?php
$form = $form ?? [];
$error = $error ?? null;
$errors = $errors ?? [];

ob_start();
?>
<div class="flex items-center justify-center px-6 py-16">
    <div class="w-full max-w-md">
        <div class="bg-white/80 backdrop-blur-md p-8 rounded-2xl border border-slate-200 shadow-sm">
            <h1 class="text-2xl font-bold text-slate-900 mb-2">Create your account</h1>
            <p class="text-slate-500 mb-6">Join FitCoch and start learning today.</p>

            <?php if ($error): ?>
                <div class="mb-4 p-4 rounded-xl bg-red-50 border border-red-200 text-red-700 text-sm">
                    <?= escape($error) ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="/register" class="space-y-4">
                <input type="hidden" name="csrf_token" value="<?= escape(csrf_token()) ?>">

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="first_name" class="block text-sm font-medium text-slate-700 mb-1">First Name</label>
                        <input
                            type="text"
                            id="first_name"
                            name="first_name"
                            value="<?= escape($form['first_name'] ?? '') ?>"
                            required
                            class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500"
                        >
                        <?php if (!empty($errors['first_name'])): ?>
                            <p class="mt-1 text-sm text-red-600"><?= escape($errors['first_name'][0]) ?></p>
                        <?php endif; ?>
                    </div>

                    <div>
                        <label for="last_name" class="block text-sm font-medium text-slate-700 mb-1">Last Name</label>
                        <input
                            type="text"
                            id="last_name"
                            name="last_name"
                            value="<?= escape($form['last_name'] ?? '') ?>"
                            required
                            class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500"
                        >
                        <?php if (!empty($errors['last_name'])): ?>
                            <p class="mt-1 text-sm text-red-600"><?= escape($errors['last_name'][0]) ?></p>
                        <?php endif; ?>
                    </div>
                </div>

                <div>
                    <label for="email" class="block text-sm font-medium text-slate-700 mb-1">Email</label>
                    <input
                        type="email"
                        id="email"
                        name="email"
                        value="<?= escape($form['email'] ?? '') ?>"
                        required
                        class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500"
                    >
                    <?php if (!empty($errors['email'])): ?>
                        <p class="mt-1 text-sm text-red-600"><?= escape($errors['email'][0]) ?></p>
                    <?php endif; ?>
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium text-slate-700 mb-1">Password</label>
                    <input
                        type="password"
                        id="password"
                        name="password"
                        required
                        class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500"
                    >
                    <?php if (!empty($errors['password'])): ?>
                        <?php foreach ($errors['password'] as $passwordError): ?>
                            <p class="mt-1 text-sm text-red-600"><?= escape($passwordError) ?></p>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="mt-1 text-xs text-slate-400">Min 10 characters with upper, lower, number, and special character.</p>
                    <?php endif; ?>
                </div>

                <div>
                    <label for="timezone" class="block text-sm font-medium text-slate-700 mb-1">Timezone</label>
                    <select
                        id="timezone"
                        name="timezone"
                        class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500"
                    >
                        <?php foreach (['UTC', 'America/New_York', 'America/Los_Angeles', 'Europe/London', 'Asia/Bangkok'] as $tz): ?>
                            <option value="<?= escape($tz) ?>" <?= ($form['timezone'] ?? 'UTC') === $tz ? 'selected' : '' ?>>
                                <?= escape($tz) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <?php if (!empty($errors['timezone'])): ?>
                        <p class="mt-1 text-sm text-red-600"><?= escape($errors['timezone'][0]) ?></p>
                    <?php endif; ?>
                </div>

                <button
                    type="submit"
                    class="w-full py-3 bg-indigo-600 text-white font-semibold rounded-xl hover:bg-indigo-700 transition-colors"
                >
                    Create Account
                </button>
            </form>

            <p class="mt-6 text-center text-sm text-slate-500">
                Already have an account?
                <a href="/login" class="text-indigo-600 hover:text-indigo-700 font-medium">Sign in</a>
            </p>
        </div>
    </div>
</div>
<?php
$content = ob_get_clean();
$showAuthLinks = false;
require base_path('app/Views/layouts/app.php');
