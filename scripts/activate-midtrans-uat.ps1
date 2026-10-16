$ErrorActionPreference = 'Stop'

$projectRoot = Split-Path -Parent $PSScriptRoot
$envPath = Join-Path $projectRoot '.env.uat'

if (-not (Test-Path -LiteralPath $envPath -PathType Leaf)) {
    throw 'File .env.uat tidak ditemukan.'
}

function Read-SecretValue([string]$Prompt) {
    $secure = Read-Host -Prompt $Prompt -AsSecureString
    $ptr = [Runtime.InteropServices.Marshal]::SecureStringToBSTR($secure)
    try {
        return [Runtime.InteropServices.Marshal]::PtrToStringBSTR($ptr)
    } finally {
        [Runtime.InteropServices.Marshal]::ZeroFreeBSTR($ptr)
    }
}

function Assert-Key([string]$Name, [string]$Value) {
    if ([string]::IsNullOrWhiteSpace($Value)) { throw "$Name tidak boleh kosong." }
    if ($Value -match '(?i)(placeholder|changeme|not-production|uat-sandbox|demo|dummy|example)') {
        throw "$Name masih terlihat seperti placeholder." 
    }
}

$content = Get-Content -LiteralPath $envPath -Raw
$serverMatches = [regex]::Matches($content, '(?m)^MIDTRANS_SERVER_KEY[ \t]*=')
$clientMatches = [regex]::Matches($content, '(?m)^MIDTRANS_CLIENT_KEY[ \t]*=')
if ($serverMatches.Count -ne 1) { throw "MIDTRANS_SERVER_KEY harus muncul tepat satu kali; ditemukan $($serverMatches.Count)." }
if ($clientMatches.Count -ne 1) { throw "MIDTRANS_CLIENT_KEY harus muncul tepat satu kali; ditemukan $($clientMatches.Count)." }

$serverKey = Read-SecretValue 'Masukkan Server Key Midtrans Sandbox baru (input tersembunyi)'
$clientKey = Read-SecretValue 'Masukkan Client Key Midtrans Sandbox baru (input tersembunyi)'
Assert-Key 'Server Key' $serverKey
Assert-Key 'Client Key' $clientKey

$newline = if ($content.Contains("`r`n")) { "`r`n" } else { "`n" }
$updated = [regex]::Replace($content, '(?m)^MIDTRANS_SERVER_KEY[ \t]*=.*$', { param($m) "MIDTRANS_SERVER_KEY=$serverKey" })
$updated = [regex]::Replace($updated, '(?m)^MIDTRANS_CLIENT_KEY[ \t]*=.*$', { param($m) "MIDTRANS_CLIENT_KEY=$clientKey" })

# Keep the existing file structure and do not create a credential backup.
[IO.File]::WriteAllText($envPath, $updated, [Text.UTF8Encoding]::new($false))

# Clear transient local variables before reporting success; do not print values.
$serverKey = $null
$clientKey = $null
$secure = $null

Write-Host 'Credential Sandbox tersimpan di .env.uat tanpa menampilkan nilainya.'
Write-Host 'Langkah berikutnya: restart server UAT port 8001, lalu minta validasi dan retest pembayaran.'
