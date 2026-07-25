# สร้าง zip สำหรับอัปโหลด manual (FileZilla / cPanel)
$ErrorActionPreference = 'Stop'
$root = Split-Path -Parent $PSScriptRoot
Set-Location $root

Write-Host 'composer install --no-dev ...'
composer install --no-dev --optimize-autoloader --quiet

& (Join-Path $PSScriptRoot 'build-migration-bundle.ps1')

$zipPath = Join-Path $root 'fitcoch-deploy.zip'
if (Test-Path $zipPath) { Remove-Item $zipPath -Force }

$excludeDirs = @('.git', 'tests', 'node_modules', '.cursor', '.agents')
$excludeNames = @('.env', '.env.udru', 'fitcoch-deploy.zip', 'deploy-test.txt', 'phpunit.xml', '.phpunit.result.cache')

Add-Type -AssemblyName System.IO.Compression.FileSystem
$zip = [System.IO.Compression.ZipFile]::Open($zipPath, 'Create')

Get-ChildItem -Path $root -Recurse -File | ForEach-Object {
    $rel = $_.FullName.Substring($root.Length + 1)
    $skip = $false
    foreach ($d in $excludeDirs) {
        if ($rel -eq $d -or $rel.StartsWith("$d\")) { $skip = $true; break }
    }
    if ($excludeNames -contains $_.Name) { $skip = $true }
    if ($skip) { return }
    [System.IO.Compression.ZipFileExtensions]::CreateEntryFromFile($zip, $_.FullName, $rel.Replace('\', '/')) | Out-Null
}

if (Test-Path '.env.production.udru.example') {
    [System.IO.Compression.ZipFileExtensions]::CreateEntryFromFile($zip, '.env.production.udru.example', '.env.example.production') | Out-Null
}

$zip.Dispose()
Write-Host "Created $zipPath"
Write-Host 'Upload + extract to ~fitcoch on server, rename .env.example.production to .env and fill secrets.'
