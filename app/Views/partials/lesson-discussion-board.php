<?php

/** @var int $discussionModuleId */

/** @var array<int, \App\Models\DiscussionPost> $discussionPosts */

/** @var bool $discussionCanPost */

/** @var string|null $discussionSuccess */

/** @var string|null $discussionError */

/** @var string $discussionRedirectUrl */



$discussionModuleId = $discussionModuleId ?? 0;

$discussionPosts = $discussionPosts ?? [];

$discussionCanPost = $discussionCanPost ?? false;

$discussionSuccess = $discussionSuccess ?? null;

$discussionError = $discussionError ?? null;

$discussionRedirectUrl = $discussionRedirectUrl ?? '';

$discussionCurrentUserId = isset($user) ? (int) $user->id : 0;

$textareaClass = 'w-full px-3 py-2.5 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-950 text-slate-900 dark:text-slate-200 text-sm focus:outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 min-h-[72px] resize-y';

?>

<div

    id="discussion-board"

    class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-4"

    data-module-id="<?= escape((string) $discussionModuleId) ?>"

    data-feed-url="<?= escape(url('/api/v1/modules/' . $discussionModuleId . '/discussions')) ?>"

    data-post-url="<?= escape(url('/modules/' . $discussionModuleId . '/discussions')) ?>"

    data-empty-text="<?= escape(__('discussion.empty')) ?>"

    data-success-text="<?= escape(__('discussion.success.posted')) ?>"

    data-current-user-id="<?= escape((string) $discussionCurrentUserId) ?>"

>

    <h3 class="text-sm font-bold text-slate-900 dark:text-white mb-1 flex items-center gap-2">

        <i class="fa-solid fa-comments text-brand-500"></i>

        <?= escape(__('discussion.board_title')) ?>

    </h3>

    <p class="text-[11px] text-slate-500 dark:text-slate-400 mb-3"><?= escape(__('discussion.board_hint')) ?></p>



    <div id="discussion-alert" class="hidden mb-3 p-3 rounded-xl text-xs"></div>



    <?php if ($discussionSuccess === 'posted'): ?>

        <div class="mb-3 p-3 rounded-xl bg-brand-500/10 border border-brand-500/20 text-brand-700 dark:text-brand-accent text-xs">

            <?= escape(__('discussion.success.posted')) ?>

        </div>

    <?php endif; ?>



    <?php if (!empty($discussionError)): ?>

        <div class="mb-3 p-3 rounded-xl bg-red-50 dark:bg-red-500/10 border border-red-200 dark:border-red-500/20 text-red-700 dark:text-red-400 text-xs">

            <?= escape($discussionError === 'csrf' ? __('errors.invalid_csrf') : ($discussionError === 'validation' ? __('errors.validation_failed') : $discussionError)) ?>

        </div>

    <?php endif; ?>



    <div id="discussion-messages" class="max-h-64 overflow-y-auto space-y-3 mb-4 pr-1 scroll-smooth" tabindex="-1" aria-live="polite">

        <?php if ($discussionPosts === []): ?>

            <p id="discussion-empty" class="text-xs text-slate-500 dark:text-slate-400 py-4 text-center border border-dashed border-slate-200 dark:border-slate-700 rounded-xl">

                <?= escape(__('discussion.empty')) ?>

            </p>

        <?php else: ?>

            <?php foreach ($discussionPosts as $index => $post): ?>

                <?php

                $isOwnPost = $post->userId === $discussionCurrentUserId;

                $isLatestPost = $index === count($discussionPosts) - 1;

                ?>

                <div

                    class="flex <?= $isOwnPost ? 'justify-end' : 'justify-start' ?>"

                    data-post-id="<?= escape((string) $post->id) ?>"

                    <?= $isLatestPost ? 'id="discussion-latest"' : '' ?>

                >

                    <article class="max-w-[88%] min-w-0 <?= $isOwnPost ? 'items-end' : 'items-start' ?> flex flex-col">

                        <?php if (!$isOwnPost): ?>

                            <span class="text-[10px] font-semibold text-slate-600 dark:text-slate-400 mb-1 px-1 truncate max-w-full">

                                <?= escape($post->authorName) ?>

                            </span>

                        <?php endif; ?>

                        <div class="<?= $isOwnPost

                            ? 'rounded-2xl rounded-br-md bg-brand-500 text-slate-950 shadow-sm shadow-brand-500/20'

                            : 'rounded-2xl rounded-bl-md bg-slate-100 dark:bg-slate-800 text-slate-800 dark:text-slate-100 border border-slate-200/80 dark:border-slate-700' ?> px-3 py-2">

                            <p class="text-sm leading-relaxed whitespace-pre-wrap break-words">

                                <?= escape($post->body) ?>

                            </p>

                        </div>

                        <time

                            class="text-[10px] text-slate-400 mt-1 px-1 <?= $isOwnPost ? 'text-right' : 'text-left' ?>"

                            datetime="<?= escape($post->createdAt) ?>"

                        >

                            <?= escape(date('d/m/Y H:i', strtotime($post->createdAt))) ?>

                        </time>

                    </article>

                </div>

            <?php endforeach; ?>

        <?php endif; ?>

    </div>



    <?php if ($discussionCanPost): ?>

        <form id="discussion-form" method="POST" action="<?= escape(url('/modules/' . $discussionModuleId . '/discussions')) ?>" class="space-y-2">

            <input type="hidden" name="csrf_token" value="<?= escape(csrf_token()) ?>">

            <input type="hidden" name="redirect" value="<?= escape($discussionRedirectUrl) ?>">

            <label class="sr-only" for="discussion-body"><?= escape(__('discussion.form.body')) ?></label>

            <textarea

                id="discussion-body"

                name="body"

                required

                maxlength="2000"

                class="<?= escape($textareaClass) ?>"

                placeholder="<?= escape(__('discussion.form.placeholder')) ?>"

            ></textarea>

            <button type="submit" class="w-full px-4 py-2.5 bg-brand-500 text-slate-950 font-bold rounded-xl hover:bg-brand-accent text-sm shadow-lg shadow-brand-500/20">

                <?= escape(__('discussion.form.submit')) ?>

            </button>

        </form>

    <?php else: ?>

        <p class="text-xs text-slate-500 dark:text-slate-400"><?= escape(__('discussion.enroll_to_post')) ?></p>

    <?php endif; ?>

</div>

<script src="<?= escape(url('/assets/discussion-board.js')) ?>" defer></script>


