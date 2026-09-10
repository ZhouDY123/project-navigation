$ErrorActionPreference = 'Stop'
$phpExe = (Get-Command php).Source
$phpRoot = Split-Path $phpExe
Write-Host 'Project Navigation started: http://localhost:8080' -ForegroundColor Green
Write-Host 'Press Ctrl+C to stop the service.' -ForegroundColor DarkGray
& $phpExe -d "extension_dir=$phpRoot\ext" -d extension=pdo_sqlite -d extension=sqlite3 -S localhost:8080 router.php
