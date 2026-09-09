$ErrorActionPreference = 'Stop'
$phpExe = (Get-Command php).Source
$phpRoot = Split-Path $phpExe
Write-Host 'DevHub 已启动：http://localhost:8080' -ForegroundColor Green
Write-Host '按 Ctrl+C 停止服务。' -ForegroundColor DarkGray
& $phpExe -d "extension_dir=$phpRoot\ext" -d extension=pdo_sqlite -d extension=sqlite3 -S localhost:8080 router.php

