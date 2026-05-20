# Registers Windows scheduled task "EspoCRM-cron-local" (every 1 minute).
# Run: powershell -ExecutionPolicy Bypass -File .\Register-CronScheduledTask.ps1
# Remove: .\Unregister-CronScheduledTask.ps1

$ErrorActionPreference = "Stop"
$toolsDir = $PSScriptRoot
$root = Split-Path -Parent $toolsDir
$cronPhp = Join-Path $root "cron.php"
if (-not (Test-Path $cronPhp)) {
    throw "cron.php not found: $cronPhp"
}

$phpCmd = Get-Command php -ErrorAction SilentlyContinue
if (-not $phpCmd) {
    throw "php not in PATH. Add PHP to PATH or edit espocrm-cron.bat manually."
}
$phpExe = $phpCmd.Source

$batPath = Join-Path $toolsDir "espocrm-cron.bat"
$batTemplate = @'
@echo off
setlocal
set "PHP_EXE=__PHP_EXE__"
set "CRON_PHP=%~dp0..\cron.php"
"%PHP_EXE%" -f "%CRON_PHP%"
exit /b %ERRORLEVEL%

'@
$batContent = $batTemplate.Replace("__PHP_EXE__", $phpExe)
Set-Content -Path $batPath -Value $batContent.TrimEnd() -Encoding ASCII

$taskName = "EspoCRM-cron-local"
$existing = Get-ScheduledTask -TaskName $taskName -ErrorAction SilentlyContinue
if ($existing) {
    Unregister-ScheduledTask -TaskName $taskName -Confirm:$false
}

$action = New-ScheduledTaskAction -Execute "cmd.exe" -Argument "/c `"$batPath`""
$start = (Get-Date).AddMinutes(1)
$trigger = New-ScheduledTaskTrigger -Once -At $start -RepetitionInterval (New-TimeSpan -Minutes 1) -RepetitionDuration (New-TimeSpan -Days 3650)
$settings = New-ScheduledTaskSettingsSet -AllowStartIfOnBatteries -DontStopIfGoingOnBatteries -StartWhenAvailable -ExecutionTimeLimit (New-TimeSpan -Minutes 10) -MultipleInstances IgnoreNew
$desc = "EspoCRM cron.php every 1 min. Root: $root"

Register-ScheduledTask -TaskName $taskName -Action $action -Trigger $trigger -Settings $settings -Description $desc | Out-Null

Write-Host "Done. Task: $taskName" -ForegroundColor Green
Write-Host "Action: cmd /c `"$batPath`""
Write-Host "Interval: every 1 minute"
Write-Host "Open taskschd.msc to run once manually for test."
