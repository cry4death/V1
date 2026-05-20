# Запуск из public: делегирует в tools\Start-Local.ps1
& (Join-Path (Split-Path -Parent $PSScriptRoot) "tools\Start-Local.ps1") @args
