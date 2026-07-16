<?php
/** @var \App\Models\User $user */
$initials = mb_strtoupper(mb_substr($user->firstName, 0, 1) . mb_substr($user->lastName, 0, 1));
?>
<div class="relative" id="header-user-menu">
    <button
        type="button"
        id="header-user-menu-btn"
        class="relative w-9 h-9 sm:w-10 sm:h-10 overflow-hidden rounded-full border-2 border-brand-500/50 bg-slate-100 dark:bg-slate-800 flex items-center justify-center shrink-0 hover:border-brand-500 hover:shadow-md hover:shadow-brand-500/20 transition-all duration-200"
        aria-label="<?= escape(__('profile.menu_label')) ?>"
        aria-haspopup="true"
        aria-expanded="false"
        aria-controls="header-user-menu-dropdown"
    >
        <i class="fa-solid fa-user-tie text-brand-600 dark:text-brand-accent text-sm"></i>
    </button>

    <div
        id="header-user-menu-dropdown"
        class="header-user-dropdown hidden absolute right-0 mt-2 w-56 rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 shadow-xl shadow-slate-900/10 dark:shadow-black/40 overflow-hidden z-[60]"
        role="menu"
        aria-labelledby="header-user-menu-btn"
    >
        <div class="px-4 py-3 border-b border-slate-200 dark:border-slate-800 bg-slate-50/80 dark:bg-slate-950/60">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-full bg-brand-500/10 text-brand-600 dark:text-brand-accent flex items-center justify-center text-xs font-bold shrink-0">
                    <?= escape($initials) ?>
                </div>
                <div class="min-w-0">
                    <p class="text-sm font-semibold text-slate-900 dark:text-white truncate"><?= escape($user->firstName . ' ' . $user->lastName) ?></p>
                    <p class="text-[11px] text-slate-500 dark:text-slate-400 truncate"><?= escape($user->email) ?></p>
                </div>
            </div>
        </div>
        <div class="py-1">
            <a
                href="<?= escape(url('/profile')) ?>"
                class="header-user-dropdown-item flex items-center gap-3 px-4 py-2.5 text-sm text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 transition"
                role="menuitem"
            >
                <i class="fa-solid fa-user-gear text-brand-500 w-4 text-center"></i>
                <?= escape(__('profile.edit_profile')) ?>
            </a>
        </div>
    </div>
</div>
