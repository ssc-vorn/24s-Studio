<?php

use App\Http\Controllers\Admin\CMS\BuilderController;
use App\Http\Controllers\Admin\CMS\MediaController;
use App\Http\Controllers\Admin\CMS\TeamController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PublicPageController;
use Illuminate\Support\Facades\Route;

Route::get('/', [PublicPageController::class, 'home'])->name('home');
Route::get('/pages/{slug}', [PublicPageController::class, 'show'])
    ->where('slug', '[A-Za-z0-9\-]+')
    ->name('public.pages.show');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('/admin/cms/organizations/{organization}/pages', [DashboardController::class, 'store'])
        ->whereUuid('organization')
        ->name('admin.cms.pages.store');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/admin/cms/organizations/{organization}/pages/{page}/builder/{version}', [BuilderController::class, 'show'])
        ->whereUuid(['organization', 'page', 'version'])
        ->scopeBindings()
        ->name('admin.cms.builder.show');
    Route::get('/admin/cms/organizations/{organization}/pages/{page}/builder/{version}/preview', [BuilderController::class, 'preview'])
        ->whereUuid(['organization', 'page', 'version'])
        ->scopeBindings()
        ->name('admin.cms.builder.preview');

    Route::get('/admin/cms/organizations/{organization}/media', [MediaController::class, 'index'])
        ->whereUuid('organization')
        ->name('admin.cms.media.index');
    Route::get('/admin/cms/organizations/{organization}/team', [TeamController::class, 'index'])
        ->whereUuid('organization')
        ->name('admin.cms.team.index');
    Route::post('/admin/cms/organizations/{organization}/team', [TeamController::class, 'store'])
        ->whereUuid('organization')
        ->name('admin.cms.team.store');
    Route::patch('/admin/cms/organizations/{organization}/team/{user}', [TeamController::class, 'update'])
        ->whereUuid('organization')
        ->name('admin.cms.team.update');
    Route::delete('/admin/cms/organizations/{organization}/team/{user}', [TeamController::class, 'destroy'])
        ->whereUuid('organization')
        ->name('admin.cms.team.destroy');
});

require __DIR__.'/auth.php';
