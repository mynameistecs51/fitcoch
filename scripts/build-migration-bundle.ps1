# รวม migration 001-020 สำหรับ import ใน phpMyAdmin (UTF-8)
$root = Split-Path -Parent $PSScriptRoot
$out = Join-Path $root 'database\migrations\all.sql'
$lines = @(
    '-- FitCoch full schema + seed (001-020)',
    '-- Import in phpMyAdmin with charset utf8mb4',
    'SET NAMES utf8mb4;',
    ''
)
foreach ($n in 1..20) {
    $prefix = '{0:D3}' -f $n
    $file = Get-ChildItem (Join-Path $root "database\migrations\${prefix}_*.sql") | Select-Object -First 1
    if (-not $file) { continue }
    $lines += "-- ===== $($file.Name) ====="
    $lines += (Get-Content $file.FullName -Raw -Encoding UTF8).TrimEnd()
    $lines += ''
}
[System.IO.File]::WriteAllText($out, ($lines -join "`r`n"), [System.Text.UTF8Encoding]::new($false))
Write-Host "Wrote $out"
