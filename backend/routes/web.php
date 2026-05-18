<?php

use App\Http\Controllers\Admin\AssignmentController;
use App\Http\Controllers\Admin\AddinConfigController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\DeviceController;
use App\Http\Controllers\Admin\ForceUpdateController;
use App\Http\Controllers\Admin\GroupController;
use App\Http\Controllers\Admin\LogController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Admin\TemplateController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware('auth')->group(function () {
    Route::redirect('/dashboard', '/admin/dashboard')->name('dashboard');
    Route::redirect('/admin', '/admin/dashboard');
    Route::get('/admin/dashboard', DashboardController::class)->name('admin.dashboard');
    Route::resource('/admin/users', UserController::class)->names('admin.users');
    Route::resource('/admin/templates', TemplateController::class)->except(['show'])->names('admin.templates');
    Route::post('/admin/templates/preview', [TemplateController::class, 'preview'])->name('admin.templates.preview');
    Route::get('/admin/assignments', [AssignmentController::class, 'index'])->name('admin.assignments.index');
    Route::post('/admin/assignments', [AssignmentController::class, 'store'])->name('admin.assignments.store');
    Route::put('/admin/assignments/{assignment}', [AssignmentController::class, 'update'])->name('admin.assignments.update');
    Route::delete('/admin/assignments/{assignment}', [AssignmentController::class, 'destroy'])->name('admin.assignments.destroy');
    Route::get('/admin/updates', [ForceUpdateController::class, 'index'])->name('admin.updates.index');
    Route::post('/admin/updates', [ForceUpdateController::class, 'store'])->name('admin.updates.store');
    Route::get('/admin/updates/{forceUpdate}', [ForceUpdateController::class, 'show'])->name('admin.updates.show');
    Route::get('/admin/logs', [LogController::class, 'index'])->name('admin.logs.index');
    Route::get('/admin/logs/{log}', [LogController::class, 'show'])->name('admin.logs.show');
    Route::get('/admin/devices', [DeviceController::class, 'index'])->name('admin.devices.index');
    Route::get('/admin/devices/{device}', [DeviceController::class, 'show'])->name('admin.devices.show');
    Route::get('/admin/deployment', [AddinConfigController::class, 'index'])->name('admin.deployment.index');
    Route::put('/admin/deployment', [AddinConfigController::class, 'update'])->name('admin.deployment.update');
    Route::post('/admin/deployment/build', [AddinConfigController::class, 'build'])->name('admin.deployment.build');
    Route::get('/admin/deployment/validation-report', [AddinConfigController::class, 'downloadValidationReport'])->name('admin.deployment.validation-report');
    Route::get('/admin/deployment/deployment-guide', [AddinConfigController::class, 'downloadDeploymentGuide'])->name('admin.deployment.deployment-guide');
    Route::get('/admin/deployment/builds/{build}/manifest', [AddinConfigController::class, 'downloadManifest'])->name('admin.deployment.manifest');
    Route::get('/admin/deployment/builds/{build}/package', [AddinConfigController::class, 'downloadPackage'])->name('admin.deployment.package');
    Route::get('/admin/settings', [SettingsController::class, 'index'])->name('admin.settings.index');
    Route::put('/admin/settings', [SettingsController::class, 'update'])->name('admin.settings.update');
    Route::resource('/admin/groups', GroupController::class)->except(['show'])->names('admin.groups');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
