<?php

$learnerName = trim($learner->firstName . ' ' . $learner->lastName);
$awardedDate = date('d M Y', strtotime($certificate->awardedAt));
?>
<!DOCTYPE html>
<html lang="<?= escape(locale()) ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= escape(__('certificates.title')) ?> — <?= escape($learnerName) ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { box-sizing: border-box; }
        body {
            margin: 0;
            font-family: "Prompt", "Sarabun", sans-serif;
            background: #fff;
            color: #111;
        }
        .frame {
            max-width: 900px;
            margin: 2rem auto;
            border: 8px solid rgba(245, 158, 11, 0.35);
            padding: 2rem;
            border-radius: 1.5rem;
        }
        .inner {
            border: 2px solid rgba(245, 158, 11, 0.25);
            padding: 3rem 2rem;
            text-align: center;
            border-radius: 1rem;
        }
        .label { letter-spacing: 0.3em; text-transform: uppercase; font-size: 0.75rem; color: #b45309; font-weight: 700; }
        h1 { font-size: 2rem; margin: 1rem 0 0.5rem; }
        .name { font-size: 2rem; color: #16a34a; font-weight: 800; margin: 0.75rem 0; }
        .course { font-size: 1.4rem; font-weight: 700; margin-top: 1rem; }
        .meta { color: #64748b; font-size: 0.95rem; margin-top: 1.5rem; }
        .hash { font-family: monospace; font-size: 0.8rem; color: #94a3b8; margin-top: 1rem; }
        .badges { margin-top: 1.5rem; display: flex; flex-wrap: wrap; gap: 0.5rem; justify-content: center; }
        .badge { padding: 0.35rem 0.75rem; border: 1px solid #fcd34d; background: #fffbeb; border-radius: 0.5rem; font-size: 0.8rem; }
        @media print {
            body { margin: 0; }
            .frame { margin: 0; border-color: #f59e0b; box-shadow: none; }
        }
    </style>
</head>
<body>
    <div class="frame">
        <div class="inner">
            <p class="label"><?= escape(__('certificates.mastery_label')) ?></p>
            <h1><?= escape(__('certificates.awarded_to')) ?></h1>
            <p class="name"><?= escape($learnerName) ?></p>
            <p class="meta"><?= escape(__('certificates.completion_text')) ?></p>
            <p class="course"><?= escape($course->title) ?></p>
            <p class="meta"><?= escape(__('certificates.awarded_on', ['date' => $awardedDate])) ?></p>
            <p class="hash"><?= escape(__('certificates.verify_code', ['hash' => $certificate->verificationHash])) ?></p>
            <?php if ($badges !== []): ?>
                <div class="badges">
                    <?php foreach ($badges as $badgeRow): ?>
                        <?php $badge = $badgeRow['badge'] ?? []; ?>
                        <span class="badge"><?= escape(__('gamification.badges.' . ($badge['name'] ?? 'unknown'))) ?></span>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
    <script>window.addEventListener('load', function () { window.print(); });</script>
</body>
</html>
