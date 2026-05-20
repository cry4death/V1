@echo off
setlocal
set "PHP_EXE=C:\php\php-8.5.3-Win32-vs17-x64\php.exe"
set "CRON_PHP=%~dp0..\cron.php"
"%PHP_EXE%" -f "%CRON_PHP%"
exit /b %ERRORLEVEL%
