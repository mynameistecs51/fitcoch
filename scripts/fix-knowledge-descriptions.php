<?php

declare(strict_types=1);

/**
 * แก้คำอธิบาย knowledge_items ที่เสีย encoding
 * รัน: php scripts/fix-knowledge-descriptions.php
 */

require_once dirname(__DIR__) . '/vendor/autoload.php';
require_once dirname(__DIR__) . '/bootstrap/helpers.php';

$envFile = dirname(__DIR__) . '/.env';
if (file_exists($envFile)) {
    foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [] as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#') || !str_contains($line, '=')) {
            continue;
        }
        [$key, $value] = explode('=', $line, 2);
        putenv(trim($key) . '=' . trim($value, " \t\n\r\0\x0B\"'"));
    }
}

$db = new App\Core\Database();
$pdo = $db->pdo();
$pdo->exec("SET NAMES utf8mb4");

$updates = [
    'Unit 1: Biomechanics & Squat Analysis' => 'ทบทวนแนวคิดหลักจาก Unit 1: Biomechanics & Squat Analysis',
    'Unit 2: Health Screening (PAR-Q+)' => 'ทบทวนแนวคิดหลักจาก Unit 2: Health Screening (PAR-Q+)',
];

$stmt = $pdo->prepare('UPDATE knowledge_items SET description = :description WHERE concept_name = :concept_name');

foreach ($updates as $concept => $description) {
    $stmt->execute([
        'concept_name' => $concept,
        'description' => $description,
    ]);
    echo "Updated: {$concept}\n";
}

$rows = $pdo->query('SELECT id, concept_name, description FROM knowledge_items')->fetchAll(PDO::FETCH_ASSOC);
foreach ($rows as $row) {
    echo "#{$row['id']} {$row['description']}\n";
}

echo "Done.\n";
