# รันไฟล์ SQL ผ่าน MySQL client ของ XAMPP (รองรับ UTF-8 / ภาษาไทย)
# ใช้: .\scripts\run-sql.ps1 database\fixes\003_fix_demo_user_names.sql

param(
    [Parameter(Mandatory = $true)]
    [string]$SqlFile
)

$mysqlCandidates = @(
    'D:\xamp\mysql\bin\mysql.exe',
    'C:\xampp\mysql\bin\mysql.exe'
)

$mysql = $mysqlCandidates | Where-Object { Test-Path $_ } | Select-Object -First 1

if (-not $mysql) {
    Write-Error 'ไม่พบ mysql.exe — ตรวจสอบว่า XAMPP ติดตั้งแล้ว'
    exit 1
}

if (-not (Test-Path $SqlFile)) {
    Write-Error "ไม่พบไฟล์: $SqlFile"
    exit 1
}

$resolved = (Resolve-Path $SqlFile).Path
$exitCode = cmd /c "`"$mysql`" -u root --default-character-set=utf8mb4 fitcoch < `"$resolved`""

if ($exitCode -ne 0) {
    exit $exitCode
}

Write-Host 'เสร็จแล้ว'
