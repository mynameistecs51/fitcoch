<?php
$thClass = 'px-3 py-2 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase';
$tdClass = 'px-3 py-2 text-sm text-slate-700 dark:text-slate-300';
ob_start();
?>
<section class="max-w-7xl mx-auto space-y-6" id="live-room"
    data-join-url="<?= escape($joinUrl) ?>"
    data-leave-url="<?= escape($leaveUrl) ?>"
    data-participants-url="<?= escape($participantsUrl) ?>"
    data-activate-url="<?= escape($activateUrl) ?>"
    data-complete-url="<?= escape($completeUrl) ?>"
    data-csrf="<?= escape(csrf_token()) ?>"
    data-is-host="<?= $isHost ? '1' : '0' ?>"
    data-session-status="<?= escape($session->status) ?>"
    data-labels="<?= escape(json_encode([
        'disconnected' => __('live.room.disconnected'),
        'connected' => __('live.room.connected'),
        'online' => __('live.room.online'),
        'presence_online' => __('live.room.presence.online'),
        'presence_left' => __('live.room.presence.left'),
        'presence_not_joined' => __('live.room.presence.not_joined'),
        'ticket_locked' => __('quizzes.ticket_status.locked'),
        'ticket_unlocked' => __('quizzes.ticket_status.unlocked'),
        'ticket_overridden' => __('quizzes.ticket_status.overridden'),
        'status_scheduled' => __('live.status.scheduled'),
        'status_active' => __('live.status.active'),
        'status_completed' => __('live.status.completed'),
        'status_cancelled' => __('live.status.cancelled'),
        'activate_success' => __('live.room.activate_success'),
        'complete_success' => __('live.room.complete_success'),
        'confirm_end' => __('live.room.confirm_end'),
    ], JSON_UNESCAPED_UNICODE)) ?>"
>
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-900 dark:text-white flex items-center gap-2 flex-wrap">
                <i class="fa-solid fa-video text-brand-500"></i>
                <span><?= escape($session->title) ?></span>
                <span id="live-status-badge" class="inline-flex px-2.5 py-1 text-xs font-semibold rounded-full bg-brand-500/10 text-brand-700 dark:text-brand-accent">
                    <?= escape(__('live.status.' . $session->status)) ?>
                </span>
            </h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1"><?= escape($course->title) ?> — <?= escape($module->title) ?></p>
        </div>
        <a href="<?= escape(url($isHost ? '/instructor/courses/' . $course->id . '/modules/' . $module->id . '/live-sessions' : '/courses/' . $course->id)) ?>" class="text-sm text-brand-600 dark:text-brand-500 hover:text-brand-accent">
            <?= escape($isHost ? __('live.instructor.back_to_sessions') : __('live.back_to_syllabus')) ?>
        </a>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
        <div class="xl:col-span-2 space-y-4">
            <div class="bg-slate-900 rounded-3xl border border-slate-800 overflow-hidden min-h-[360px] relative">
                <video id="live-local-video" class="w-full h-full min-h-[360px] object-cover bg-slate-950" autoplay muted playsinline></video>
                <div id="live-video-placeholder" class="absolute inset-0 flex flex-col items-center justify-center text-slate-400 p-6 text-center">
                    <i class="fa-solid fa-video text-4xl mb-3 text-brand-500"></i>
                    <p class="text-sm"><?= escape(__('live.room.video_placeholder')) ?></p>
                </div>
                <?php if ($isHost): ?>
                    <div class="absolute bottom-4 left-1/2 -translate-x-1/2 flex items-center gap-2 bg-slate-950/80 backdrop-blur px-4 py-2 rounded-2xl border border-slate-700">
                        <button type="button" id="live-toggle-camera" class="w-10 h-10 rounded-xl bg-slate-800 text-white hover:bg-brand-500 transition" title="<?= escape(__('live.room.toggle_camera')) ?>">
                            <i class="fa-solid fa-video"></i>
                        </button>
                        <button type="button" id="live-toggle-mic" class="w-10 h-10 rounded-xl bg-slate-800 text-white hover:bg-brand-500 transition" title="<?= escape(__('live.room.toggle_mic')) ?>">
                            <i class="fa-solid fa-microphone"></i>
                        </button>
                    </div>
                <?php endif; ?>
            </div>

            <?php if ($isHost): ?>
                <div class="bg-white dark:bg-slate-900 p-4 md:p-6 rounded-3xl border border-slate-200 dark:border-slate-800">
                    <h2 class="text-sm font-bold text-slate-900 dark:text-white mb-3 flex items-center">
                        <i class="fa-solid fa-sliders text-brand-500 mr-2"></i>
                        <?= escape(__('live.room.host_controls')) ?>
                    </h2>
                    <div class="flex flex-wrap gap-3">
                        <button type="button" id="live-activate-btn" class="px-5 py-2.5 bg-brand-500 text-slate-950 font-bold rounded-xl hover:bg-brand-accent transition <?= $session->status !== 'scheduled' ? 'opacity-50 cursor-not-allowed' : '' ?>" <?= $session->status !== 'scheduled' ? 'disabled' : '' ?>>
                            <i class="fa-solid fa-circle-play mr-1"></i><?= escape(__('live.room.start_broadcast')) ?>
                        </button>
                        <button type="button" id="live-complete-btn" class="px-5 py-2.5 border border-red-300 dark:border-red-500/30 text-red-700 dark:text-red-400 font-bold rounded-xl hover:bg-red-50 dark:hover:bg-red-500/10 transition <?= $session->status !== 'active' ? 'opacity-50 cursor-not-allowed' : '' ?>" <?= $session->status !== 'active' ? 'disabled' : '' ?>>
                            <i class="fa-solid fa-stop mr-1"></i><?= escape(__('live.room.end_broadcast')) ?>
                        </button>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <div class="space-y-4">
            <div class="bg-white dark:bg-slate-900 p-6 rounded-3xl border border-slate-200 dark:border-slate-800 space-y-3">
                <p id="live-connection-status" class="text-sm text-slate-600 dark:text-slate-300"><?= escape(__('live.room.disconnected')) ?></p>
                <button id="live-join-btn" type="button" class="w-full py-3 bg-brand-500 text-slate-950 font-bold rounded-xl hover:bg-brand-accent transition">
                    <?= escape($isHost ? __('live.room.join_as_host') : __('live.room.join_session')) ?>
                </button>
                <button id="live-leave-btn" type="button" hidden class="w-full py-3 border border-slate-300 dark:border-slate-700 text-slate-700 dark:text-slate-200 font-bold rounded-xl hover:bg-slate-50 dark:hover:bg-slate-800 transition">
                    <?= escape(__('live.room.leave_session')) ?>
                </button>
            </div>

            <?php if ($isHost): ?>
                <div class="bg-white dark:bg-slate-900 p-6 rounded-3xl border border-slate-200 dark:border-slate-800">
                    <div class="flex items-center justify-between mb-3">
                        <h2 class="text-sm font-bold text-slate-900 dark:text-white"><?= escape(__('live.room.participants')) ?></h2>
                        <span id="live-online-count" class="text-xs font-semibold text-brand-600 dark:text-brand-accent">0 <?= escape(__('live.room.online')) ?></span>
                    </div>
                    <div class="max-h-80 overflow-y-auto rounded-2xl border border-slate-200 dark:border-slate-800">
                        <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-800">
                            <thead class="bg-slate-50 dark:bg-slate-950 sticky top-0">
                                <tr>
                                    <th class="<?= escape($thClass) ?>"><?= escape(__('live.room.learner')) ?></th>
                                    <th class="<?= escape($thClass) ?>"><?= escape(__('live.room.ticket')) ?></th>
                                    <th class="<?= escape($thClass) ?>"><?= escape(__('live.room.presence_label')) ?></th>
                                </tr>
                            </thead>
                            <tbody id="live-participants-body" class="divide-y divide-slate-200 dark:divide-slate-800">
                                <?php foreach ($roster as $row): ?>
                                    <tr>
                                        <td class="<?= escape($tdClass) ?>">
                                            <p class="font-medium text-slate-900 dark:text-slate-200"><?= escape($row['first_name'] . ' ' . $row['last_name']) ?></p>
                                            <p class="text-xs text-slate-400"><?= escape($row['email']) ?></p>
                                        </td>
                                        <td class="<?= escape($tdClass) ?> text-xs"><?= escape(__('quizzes.ticket_status.' . $row['ticket_status'])) ?></td>
                                        <td class="<?= escape($tdClass) ?> text-xs font-semibold"><?= escape(__('live.room.presence.' . $row['presence'])) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php else: ?>
                <div class="bg-white dark:bg-slate-900 p-6 rounded-3xl border border-slate-200 dark:border-slate-800">
                    <p class="text-xs uppercase text-slate-500 dark:text-slate-400"><?= escape(__('live.room.room_id')) ?></p>
                    <p class="font-mono text-xs text-slate-700 dark:text-slate-300 break-all mt-1"><?= escape($session->roomId) ?></p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>
<script src="<?= escape(url('/assets/live-room.js')) ?>"></script>
<?php
$content = ob_get_clean();
$showAuthLinks = false;
$showSidebar = true;
$currentNav = $isHost ? 'instructor' : 'courses';
require base_path('app/Views/layouts/app.php');
