<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SiteController;
use App\Http\Controllers\DatabaseController;

Route::get('/', function () {
    return redirect()->route('sites.index');
});

Route::get('/sites/check-git', [SiteController::class, 'checkGit'])->name('sites.check-git');
Route::get('/sites', [SiteController::class, 'index'])->name('sites.index');
Route::post('/sites', [SiteController::class, 'store'])->name('sites.store');
Route::get('/sites/env/{site}', [SiteController::class, 'editEnv'])->name('sites.env');
Route::post('/sites/env/{site}', [SiteController::class, 'saveEnv'])->name('sites.save-env');
Route::delete('/sites/{site}', [SiteController::class, 'destroy'])->name('sites.destroy');
Route::post('/sites/restart-service', [SiteController::class, 'restartService'])->name('sites.restart-service');
Route::get('/sites/installation-logs', [SiteController::class, 'installationLogs'])->name('sites.installation-logs');
Route::get('/installation-logs', [SiteController::class, 'getInstallationLogs']);
Route::get('/sites/{site}/terminal', [SiteController::class, 'openInTerminal'])->name('sites.terminal');
Route::get('/sites/{site}/folder', [SiteController::class, 'openInFolder'])->name('sites.folder');

use App\Http\Controllers\SoftwareController;
Route::get('/software', [SoftwareController::class, 'index'])->name('software.index');
Route::post('/software', [SoftwareController::class, 'install'])->name('software.install');

use App\Http\Controllers\ServicesController;
Route::get('/services', [ServicesController::class, 'index'])->name('services.index');
Route::post('/services/restart', [ServicesController::class, 'restart'])->name('services.restart');
Route::get('/services/logs/{type?}', [ServicesController::class, 'logs'])->name('services.logs');
Route::post('/services/logs/clear', [ServicesController::class, 'clearLogs'])->name('services.logs-clear');
Route::get('/services/php', [ServicesController::class, 'phpIni'])->name('services.php');
Route::post('/services/php', [ServicesController::class, 'savePhpIni'])->name('services.save-php');

Route::get('/databases', [DatabaseController::class, 'index'])->name('databases.index');
Route::post('/databases', [DatabaseController::class, 'store'])->name('databases.store');
Route::post('/databases/create', [DatabaseController::class, 'createDatabase'])->name('databases.create');
