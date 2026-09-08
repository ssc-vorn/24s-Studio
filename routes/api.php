<?php

use App\Http\Controllers\Api\V1\CMS\MediaController;
use App\Http\Controllers\Api\V1\CMS\PageController;
use App\Http\Controllers\Api\V1\CMS\PageSectionAutosaveController;
use App\Http\Controllers\Api\V1\CMS\PageSectionController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')
    ->middleware(['auth:sanctum', 'throttle:60,1'])
    ->scopeBindings()
    ->group(function () {
        Route::prefix('organizations/{organization}')
            ->whereUuid('organization')
            ->group(function () {
                Route::apiResource('pages', PageController::class)->whereUuid('page');
                Route::apiResource('media', MediaController::class)
                    ->only(['index', 'store', 'update', 'destroy'])
                    ->whereUuid('media');

                Route::get('pages/{page}/versions', [PageController::class, 'versions'])
                    ->whereUuid('page')->name('api.v1.pages.versions.index');
                Route::post('pages/{page}/versions', [PageController::class, 'createVersion'])
                    ->whereUuid('page')->name('api.v1.pages.versions.store');
                Route::post('pages/{page}/versions/{version}/restore', [PageController::class, 'restoreVersion'])
                    ->whereUuid(['page', 'version'])->name('api.v1.pages.versions.restore');
                Route::post('pages/{page}/versions/{version}/submit-review', [PageController::class, 'submitReview'])
                    ->whereUuid(['page', 'version'])->name('api.v1.pages.versions.submit-review');
                Route::post('pages/{page}/versions/{version}/approve', [PageController::class, 'approve'])
                    ->whereUuid(['page', 'version'])->name('api.v1.pages.versions.approve');
                Route::post('pages/{page}/versions/{version}/publish', [PageController::class, 'publish'])
                    ->whereUuid(['page', 'version'])->name('api.v1.pages.versions.publish');

                Route::prefix('pages/{page}/versions/{version}')
                    ->whereUuid(['page', 'version'])
                    ->group(function () {
                        Route::post('sections/autosave', PageSectionAutosaveController::class)
                            ->name('api.v1.page-sections.autosave');
                        Route::post('sections/reorder', [PageSectionController::class, 'reorder'])
                            ->name('api.v1.page-sections.reorder');
                        Route::apiResource('sections', PageSectionController::class)
                            ->whereUuid('section');
                    });
            });
    });
