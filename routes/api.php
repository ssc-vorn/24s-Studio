<?php

use App\Http\Controllers\Api\V1\CMS\PageController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')
    ->middleware(['auth:sanctum', 'throttle:60,1'])
    ->scopeBindings()
    ->group(function () {
        Route::prefix('organizations/{organization}')
            ->whereUuid('organization')
            ->group(function () {
                Route::apiResource('pages', PageController::class)
                    ->whereUuid('page');

                Route::get('pages/{page}/versions', [PageController::class, 'versions'])
                    ->whereUuid(['page'])
                    ->name('api.v1.pages.versions.index');

                Route::post('pages/{page}/versions', [PageController::class, 'createVersion'])
                    ->whereUuid(['page'])
                    ->name('api.v1.pages.versions.store');

                Route::post('pages/{page}/versions/{version}/publish', [PageController::class, 'publish'])
                    ->whereUuid(['page', 'version'])
                    ->name('api.v1.pages.versions.publish');
            });
    });
