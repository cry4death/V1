# Удаляет задачу Планировщика, созданную Register-CronScheduledTask.ps1
$ErrorActionPreference = "Stop"
$taskName = "EspoCRM-cron-local"
$t = Get-ScheduledTask -TaskName $taskName -ErrorAction SilentlyContinue
if (-not $t) {
    Write-Host "Задача не найдена: $taskName" -ForegroundColor Yellow
    exit 0
}
Unregister-ScheduledTask -TaskName $taskName -Confirm:$false
Write-Host "Удалено: $taskName" -ForegroundColor Green
