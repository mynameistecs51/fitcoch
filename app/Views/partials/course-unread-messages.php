<?php
/** @var int $courseId */
/** @var int $unreadCount */
/** @var string $linkUrl */
$unreadCount = (int) ($unreadCount ?? 0);
$linkUrl = $linkUrl ?? url('/courses/' . ($courseId ?? 0));
?>
<td class="px-4 py-3 text-sm text-center whitespace-nowrap">
    <?php if ($unreadCount > 0): ?>
        <a
            href="<?= escape($linkUrl) ?>"
            class="inline-flex items-center justify-center min-w-[2rem] h-8 px-2 rounded-full bg-red-500 text-white text-xs font-bold shadow-sm shadow-red-500/30 hover:bg-red-600 transition"
            title="<?= escape(__('discussion.unread_badge', ['count' => (string) $unreadCount])) ?>"
        >
            <i class="fa-solid fa-comment-dots mr-1"></i>
            <?= escape((string) $unreadCount) ?>
        </a>
    <?php else: ?>
        <span class="inline-flex items-center justify-center w-8 h-8 rounded-full text-slate-300 dark:text-slate-600" aria-hidden="true">
            <i class="fa-regular fa-comment"></i>
        </span>
    <?php endif; ?>
</td>
