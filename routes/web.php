<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\AdminController;
// use App\Http\Controllers\UserController;
use App\Http\Controllers\CampaignController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\SettingsController;



Route::get('/', function () {
    return redirect('/wa/login');
});



Route::prefix('wa')->group(function () {

    Route::get('login', [LoginController::class, 'showLoginForm'])
        ->name('login'); // 👈 FIX HERE

    Route::post('login', [LoginController::class, 'login'])
        ->name('wa.login.submit');

    Route::post('logout', function () {
        Auth::logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();
        return redirect()->route('login'); // 👈 FIX HERE
    })->name('wa.logout');
});

Route::prefix('wa')
    ->middleware(['auth'])
    ->group(function () {

        // Dashboard
        Route::get('dashboard', [AdminController::class, 'dashboard'])
            ->name('wa.dashboard');

        // Route::resource('users', UserController::class);
        Route::resource('clients', ClientController::class);

        Route::resource('campaigns', CampaignController::class);

        Route::get('campaigns/export/{id}', [CampaignController::class, 'export'])
            ->name('campaigns.export');

        Route::get('settings', [SettingsController::class, 'index'])
            ->name('settings.index');

        Route::put('settings/app', [SettingsController::class, 'updateApp'])
            ->name('settings.app.update');

        Route::put('settings/profile', [SettingsController::class, 'updateProfile'])
            ->name('settings.profile.update');

        Route::put('settings/password', [SettingsController::class, 'updatePassword'])
            ->name('settings.password.update');

        Route::post('campaigns/{id}/send', [CampaignController::class, 'sendCampaign']);
    });
