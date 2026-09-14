<?php

use App\Http\Controllers\Api\V1\OrganizationController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// GET  /organizations/{id}  — получить организацию и статус
// GET  /organizations/{id}/reviews?page=1 — отзывы

Route::prefix('v1')
    ->middleware('auth:sanctum')
    ->group(function () {
        Route::post('organizations', [OrganizationController::class, 'store'])
            ->name('organizations.store');
        Route::get('organizations/{organization}', [OrganizationController::class, 'show'])
            ->name('organizations.show');
        Route::get('organizations/{organization}/reviews', [OrganizationController::class, 'reviews'])
            ->name('organizations.reviews');
    });
