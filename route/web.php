<?php

use Amplify\System\installer\Controllers\DatabaseController;
use Amplify\System\Installer\Controllers\EnvironmentController;
use Amplify\System\Installer\Controllers\FinalController;
use Amplify\System\Installer\Controllers\PermissionsController;
use Amplify\System\Installer\Controllers\RequirementsController;
use Amplify\System\Installer\Controllers\UpdateController;
use Amplify\System\Installer\Controllers\WelcomeController;
use Illuminate\Support\Facades\Route;

Route::prefix('install')->name('installer.')->middleware(['web', 'install'])->group(function () {
    Route::get('/', [WelcomeController::class, 'welcome'])->name('welcome');
    Route::get('environment', [EnvironmentController::class, 'environmentMenu'])->name('environment');
    Route::get('environment/wizard', [EnvironmentController::class, 'environmentWizard'])->name('environmentWizard');
    Route::post('environment/saveWizard', [EnvironmentController::class, 'saveWizard'])->name('environmentSaveWizard');
    Route::get('environment/classic', [EnvironmentController::class, 'environmentClassic'])->name('environmentClassic');
    Route::post('environment/saveClassic', [EnvironmentController::class, 'saveClassic'])->name('environmentSaveClassic');
    Route::get('requirements', [RequirementsController::class, 'requirements'])->name('requirements');
    Route::get('permissions', [PermissionsController::class, 'permissions'])->name('permissions');
    Route::get('database', [DatabaseController::class, 'database'])->name('database');
    Route::get('final', [FinalController::class, 'finish'])->name('final');
});

Route::prefix('update')->name('updater.')->middleware(['web'])->group(function () {
    Route::group(['middleware' => 'update'], function () {
        Route::get('/', [UpdateController::class, 'welcome'])->name('welcome');
        Route::get('overview', [UpdateController::class, 'overview'])->name('overview');
        Route::get('database', [UpdateController::class, 'database'])->name('database');
    });

    // This needs to be out of the middleware because right after the migration has been
    // run, the middleware sends a 404.
    Route::get('final', [UpdateController::class, 'finish'])->name('final');
});
