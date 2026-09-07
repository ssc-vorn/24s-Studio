<?php

use App\Http\Controllers\Admin\CMS\BuilderController;
use App\Http\Controllers\Admin\CMS\MediaController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PublicPageController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', [PublicPageController::class, 'home'])->name('home');
Route::get('/pages/{slug}', [PublicPageController::class, 'show'])->where('slug', '[A-Za-z0-9\-]+')->name('public.pages.show');

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/admin/cms/organizations/{organization}/pages/{page}/builder/{version}', [BuilderController::class, 'show'])
        ->whereUuid(['organization', 'page', 'version'])
        ->scopeBindings()
        ->name('admin.cms.builder.show');

    Route::get('/admin/cms/organizations/{organization}/media', [MediaController::class, 'index'])
        ->whereUuid('organization')
        ->name('admin.cms.media.index');
});

require __DIR__.'/auth.php';
