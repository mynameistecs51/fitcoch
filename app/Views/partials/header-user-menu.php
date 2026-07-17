<?php
/** @var \App\Models\User $user */
$initials = mb_strtoupper(mb_substr($user->firstName, 0, 1) . mb_substr($user->lastName, 0, 1));
?>
<div class="relative" id="header-user-menu">
    <button
        type="button"
        id="header-user-menu-btn"
        class="relative w-9 h-9 sm:w-10 sm:h-10 overflow-hidden rounded-full border-2 border-brand-500/40 bg-gradient-to-br from-brand-500/15 to-brand-accent/10 flex items-center justify-center shrink-0 hover:border-brand-500 hover:shadow-lg hover:shadow-brand-500/25 transition-all duration-200"
        aria-label="<?= escape(__('profile.menu_label')) ?>"
        aria-haspopup="true"
        aria-expanded="false"
        aria-controls="header-user-menu-dropdown"
    >
        <span class="text-xs sm:text-sm font-bold text-brand-700 dark:text-brand-accent"><?= escape($initials) ?></span>
    </button>

    <div
        id="header-user-menu-dropdown"
        class="header-user-dropdown hidden absolute right-0 mt-2 w-60 rounded-2xl border border-slate-200/80 dark:border-slate-700/80 bg-white/95 dark:bg-slate-900/95 backdrop-blur-xl shadow-xl shadow-slate-900/10 dark:shadow-black/40 overflow-hidden z-[60]"
        role="menu"
        aria-labelledby="header-user-menu-btn"
    >
        <div class="px-4 py-3.5 border-b border-slate-200/80 dark:border-slate-800/80 bg-gradient-to-r from-brand-500/5 to-transparent">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-full bg-gradient-to-br from-brand-500 to-brand-accent text-slate-950 flex items-center justify-center text-sm font-extrabold shrink-0 shadow-md shadow-brand-500/20">
                    <?= escape($initials) ?>
                </div>
                <div class="min-w-0">
                    <p class="text-sm font-semibold text-slate-900 dark:text-white truncate"><?= escape($user->fullName()) ?></p>
                    <p class="text-[11px] text-slate-500 dark:text-slate-400 truncate"><?= escape($user->studentId ?? $user->email) ?></p>
                </div>
            </div>
        </div>
        <div class="py-1.5">
            <a
                href="<?= escape(url('/profile')) ?>"
                class="header-user-dropdown-item flex items-center gap-3 px-4 py-2.5 text-sm text-slate-700 dark:text-slate-200 hover:bg-slate-100/80 dark:hover:bg-slate-800/80 transition"
                role="menuitem"
            >
                <span class="flex items-center justify-center w-8 h-8 rounded-lg bg-brand-500/10 shrink-0">
                    <i class="fa-solid fa-user-gear text-brand-600 dark:text-brand-accent text-sm"></i>
                </span>
                <?= escape(__('profile.edit_profile')) ?>
            </a>
            <form method="POST" action="<?= escape(url('/logout')) ?>" role="none">
                <input type="hidden" name="csrf_token" value="<?= escape(csrf_token()) ?>">
                <button
                    type="submit"
                    class="header-user-dropdown-item flex items-center gap-3 w-full px-4 py-2.5 text-sm text-red-600 dark:text-red-400 hover:bg-red-500/10 transition text-left"
                    role="menuitem"
                >
                    <span class="flex items-center justify-center w-8 h-8 rounded-lg bg-red-500/10 shrink-0">
                        <i class="fa-solid fa-right-from-bracket text-red-500 text-sm"></i>
                    </span>
                    <?= escape(__('nav.sign_out')) ?>
                </button>
            </form>
        </div>
    </div>
</div>
