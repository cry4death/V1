# Запуск EspoCRM на встроенном сервере PHP (Windows) с локальной MySQL.
# Перед первым запуском создайте базу и пользователя в MySQL, например в MySQL Workbench или консоли:
#
#   CREATE DATABASE espocrm CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
#   CREATE USER 'espocrm'@'localhost' IDENTIFIED BY 'ваш_пароль';
#   GRANT ALL PRIVILEGES ON espocrm.* TO 'espocrm'@'localhost';
#   FLUSH PRIVILEGES;
#
# В мастере установки (http://127.0.0.1:8080/install/):
#   Хост БД: 127.0.0.1:3306  (или localhost:3306, если порт другой — укажите свой)
#   Имя БД / пользователь / пароль — как создали выше
#   Site URL: http://127.0.0.1:8080  (тот же адрес, по которому открываете браузер)

param(
    [string]$Listen = "127.0.0.1:8080"
)

$ErrorActionPreference = "Stop"
$toolsDir = $PSScriptRoot
$root = Split-Path -Parent $toolsDir

& (Join-Path $toolsDir "Ensure-ClientJunction.ps1")

$public = Join-Path $root "public"
$router = Join-Path $public "router.php"
if (-not (Test-Path $router)) {
    throw "Не найден $router"
}

Write-Host ""
Write-Host "Сервер: http://$Listen/" -ForegroundColor Green
Write-Host "Установка: http://$Listen/install/" -ForegroundColor Green
Write-Host "Остановка: Ctrl+C" -ForegroundColor Yellow
Write-Host ""

Set-Location $public
php -S $Listen router.php
