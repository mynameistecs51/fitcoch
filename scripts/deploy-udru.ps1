# Deploy FitCoch ไป UDRU shared hosting ผ่าน FTP
# ใช้: .\scripts\deploy-udru.ps1
# ตัวแปร (optional): $env:FITCOCH_FTP_USER, $env:FITCOCH_FTP_PASS

param(
    [string]$FtpHost = 'hosting.udru.ac.th',
    [string]$FtpUser = $(if ($env:FITCOCH_FTP_USER) { $env:FITCOCH_FTP_USER } else { 'fitcoch' }),
    [string]$FtpPass = $(if ($env:FITCOCH_FTP_PASS) { $env:FITCOCH_FTP_PASS } else { '' }),
    [string]$RemoteRoot = '/',
    [string]$EnvFile = '.env.udru'
)

$ErrorActionPreference = 'Stop'
$root = Split-Path -Parent $PSScriptRoot
Set-Location $root

if ($FtpPass -eq '') {
    $secure = Read-Host 'FTP password' -AsSecureString
    $FtpPass = [Runtime.InteropServices.Marshal]::PtrToStringAuto(
        [Runtime.InteropServices.Marshal]::SecureStringToBSTR($secure)
    )
}

Write-Host 'Installing production dependencies...'
composer install --no-dev --optimize-autoloader --quiet

$excludeDirs = @('.git', 'tests', 'node_modules', '.cursor', '.agents')
$excludeFiles = @('.env', '.env.udru', 'phpunit.xml', '.phpunit.result.cache')

function Test-ExcludedPath {
    param([string]$RelativePath)
    foreach ($part in $excludeDirs) {
        if ($RelativePath -eq $part -or $RelativePath.StartsWith("$part\")) {
            return $true
        }
    }
    $name = Split-Path -Leaf $RelativePath
    return $excludeFiles -contains $name
}

function Send-FtpFile {
    param(
        [string]$LocalPath,
        [string]$RemotePath
    )
    $uri = "ftp://${FtpHost}${RemotePath}"
    $request = [System.Net.FtpWebRequest]::Create($uri)
    $request.Method = [System.Net.WebRequestMethods+Ftp]::UploadFile
    $request.Credentials = New-Object System.Net.NetworkCredential($FtpUser, $FtpPass)
    $request.UseBinary = $true
    $request.UsePassive = $true
    $bytes = [System.IO.File]::ReadAllBytes($LocalPath)
    $request.ContentLength = $bytes.Length
    $stream = $request.GetRequestStream()
    $stream.Write($bytes, 0, $bytes.Length)
    $stream.Close()
    $response = $request.GetResponse()
    $response.Close()
}

function Ensure-FtpDirectory {
    param([string]$RemotePath)
    $uri = "ftp://${FtpHost}${RemotePath}"
    try {
        $request = [System.Net.FtpWebRequest]::Create($uri)
        $request.Method = [System.Net.WebRequestMethods+Ftp]::MakeDirectory
        $request.Credentials = New-Object System.Net.NetworkCredential($FtpUser, $FtpPass)
        $request.UsePassive = $true
        $response = $request.GetResponse()
        $response.Close()
    } catch {
        # already exists
    }
}

Write-Host "Uploading to ftp://${FtpHost}${RemoteRoot} ..."

$files = Get-ChildItem -Path $root -Recurse -File | Where-Object {
    $rel = $_.FullName.Substring($root.Length + 1)
    -not (Test-ExcludedPath $rel)
}

$total = $files.Count
$i = 0
foreach ($file in $files) {
    $i++
    $rel = $file.FullName.Substring($root.Length + 1).Replace('\', '/')
    $remote = ($RemoteRoot.TrimEnd('/') + '/' + $rel).Replace('//', '/')
    $remoteDir = Split-Path -Parent $remote
    if ($remoteDir -and $remoteDir -ne '/') {
        $parts = $remoteDir.Trim('/').Split('/')
        $built = ''
        foreach ($part in $parts) {
            $built += "/$part"
            Ensure-FtpDirectory $built
        }
    }
    Write-Progress -Activity 'Uploading FitCoch' -Status $rel -PercentComplete (($i / $total) * 100)
    Send-FtpFile -LocalPath $file.FullName -RemotePath $remote
}
Write-Progress -Activity 'Uploading FitCoch' -Completed

if (Test-Path $EnvFile) {
    Write-Host "Uploading $EnvFile as .env ..."
    $tempEnv = Join-Path $env:TEMP 'fitcoch-env-upload'
    Copy-Item $EnvFile $tempEnv -Force
    Send-FtpFile -LocalPath $tempEnv -RemotePath ($RemoteRoot.TrimEnd('/') + '/.env')
    Remove-Item $tempEnv -Force
} else {
    Write-Warning "ไม่พบ $EnvFile — สร้างจาก .env.production.udru.example แล้วรันใหม่"
}

Write-Host "Deploy เสร็จ — เปิด https://hosting.udru.ac.th/~fitcoch/public/"
Write-Host 'อย่าลืมรัน migration ใน phpMyAdmin (ดู docs/DEPLOY_UDRU.md)'
