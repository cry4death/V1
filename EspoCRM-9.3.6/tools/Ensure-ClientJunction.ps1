# Создаёт связь public\client -> ..\client для встроенного сервера PHP и IIS.
$ErrorActionPreference = "Stop"
$root = Split-Path -Parent (Split-Path -Parent $MyInvocation.MyCommand.Path)
$target = Join-Path $root "client"
$link = Join-Path $root "public\client"

if (-not (Test-Path $target)) {
    throw "Не найдена папка client: $target"
}

if (Test-Path $link) {
    $item = Get-Item $link -Force
    if ($item.Attributes -band [IO.FileAttributes]::ReparsePoint) {
        Write-Host "Junction уже есть: $link"
        exit 0
    }
    throw "Путь занят (не junction): $link"
}

New-Item -ItemType Junction -Path $link -Target $target | Out-Null
Write-Host "Создан junction: $link -> $target"
