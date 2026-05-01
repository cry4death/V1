<?php

use App\Http\Controllers\Api\V1\ArticleController;
use App\Http\Controllers\Api\V1\DoctorController;
use App\Http\Controllers\Api\V1\PromotionController;
use App\Http\Controllers\Api\V1\ServiceDirectionController;
use Illuminate\Support\Facades\Route;

Route::get('/doctors', [DoctorController::class, 'index']);
Route::get('/doctors/{slug}', [DoctorController::class, 'show'])->where('slug', '[a-z0-9\-]+');

Route::get('/service-directions', [ServiceDirectionController::class, 'index']);
Route::get('/service-directions/{slug}/services', [ServiceDirectionController::class, 'services'])
    ->where('slug', '[a-z0-9\-]+');

Route::get('/article-categories', [ArticleController::class, 'categories']);
Route::get('/articles', [ArticleController::class, 'index']);
Route::get('/articles/{slug}', [ArticleController::class, 'show'])->where('slug', '[a-z0-9\-]+');

Route::get('/promotions', [PromotionController::class, 'index']);
Route::get('/promotions/{slug}', [PromotionController::class, 'show'])->where('slug', '[a-z0-9\-]+');
