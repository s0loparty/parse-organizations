<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\OrganizationController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('login', [AuthController::class, 'login'])->name('login');

Route::middleware('auth:sanctum')
    ->group(function (): void {
        Route::post('logout', [AuthController::class, 'logout'])
            ->name('logout');

        Route::get('user', function (Request $request) {
            return $request->user();
        })->name('user.show');

        Route::prefix('v1')->group(function (): void {
            Route::get('organizations', [OrganizationController::class, 'index'])
                ->name('organizations.index');

            Route::post('organizations', [OrganizationController::class, 'store'])
                ->name('organizations.store');

            Route::get('organizations/{organization}', [OrganizationController::class, 'show'])
                ->name('organizations.show');

            Route::get('organizations/{organization}/reviews', [OrganizationController::class, 'reviews'])
                ->name('organizations.reviews');
        });
    });
