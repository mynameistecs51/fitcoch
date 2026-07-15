# รันไฟล์ SQL ผ่าน MySQL client ของ XAMPP
# ใช้: .\scripts\run-sql.ps1 database\fixes\001_fix_cohort_name_encoding.sql

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

Get-Content -Path $SqlFile -Encoding UTF8 | & $mysql -u root --default-character-set=utf8mb4 fitcoch
Write-Host 'เสร็จแล้ว'
