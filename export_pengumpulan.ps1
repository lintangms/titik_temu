param (
    [string]$KelompokNomor = "1"
)

$sourceDir = $PSScriptRoot
$parentDir = Split-Path $sourceDir -Parent
$zipName = "KELOMPOK$KelompokNomor.zip"
$zipOutput = Join-Path $parentDir $zipName

Write-Host "Sedang menyiapkan arsip pengumpulan ($zipName)..." -ForegroundColor Cyan

# 1. Pastikan file INFO_KELOMPOKX.txt dan Laporan_KELOMPOKX.pdf siap
$infoSource = Join-Path $sourceDir "INFO_KELOMPOK.txt"
$infoTarget = Join-Path $sourceDir "INFO_KELOMPOK$KelompokNomor.txt"
if (Test-Path $infoSource) {
    Copy-Item $infoSource $infoTarget -Force
}

$pdfSource = Join-Path $sourceDir "Laporan_KELOMPOK.pdf"
$pdfTarget = Join-Path $sourceDir "Laporan_KELOMPOK$KelompokNomor.pdf"
if (Test-Path $pdfSource) {
    Copy-Item $pdfSource $pdfTarget -Force
}

# 2. Hapus zip lama jika ada
if (Test-Path $zipOutput) {
    Remove-Item $zipOutput -Force
}

# 3. Daftar folder/file yang wajib dikecualikan sesuai panduan dosen (vendor & node_modules dilarang agar ringan)
$exclude = @("vendor", "node_modules", ".git", ".phpunit.cache", "storage\framework\cache\data")

# 4. Copy ke temporary clean folder
$tempDir = Join-Path $env:TEMP "kelompok_${KelompokNomor}_clean_$(Get-Random)"
New-Item -ItemType Directory -Path $tempDir -Force | Out-Null

Get-ChildItem -Path $sourceDir -Force | Where-Object {
    $name = $_.Name
    $exclude -notcontains $name -and $name -notlike "kelompok_*.zip"
} | ForEach-Object {
    Copy-Item -Path $_.FullName -Destination $tempDir -Recurse -Force
}

# Pastikan file penting tercopy
if (Test-Path (Join-Path $sourceDir ".env")) {
    Copy-Item (Join-Path $sourceDir ".env") $tempDir -Force
}
if (Test-Path (Join-Path $sourceDir ".env.example")) {
    Copy-Item (Join-Path $sourceDir ".env.example") $tempDir -Force
}

# 5. Compress menjadi KELOMPOKX.zip
Compress-Archive -Path "$tempDir\*" -DestinationPath $zipOutput -Force

# 6. Bersihkan temporary
Remove-Item -Path $tempDir -Recurse -Force

Write-Host "=================================================================" -ForegroundColor Green
Write-Host "BERHASIL! Arsip final pengumpulan telah dibuat di:" -ForegroundColor Green
Write-Host $zipOutput -ForegroundColor Yellow
$sizeMb = [math]::Round(((Get-Item $zipOutput).Length / 1MB), 2)
Write-Host "Ukuran file: $sizeMb MB (sangat ringan, siap diunggah ke portal kuliah/BRONE)" -ForegroundColor Green
Write-Host "=================================================================" -ForegroundColor Green
