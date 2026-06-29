<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

use App\Http\Controllers\ChatbotHistoryController;
use App\Http\Controllers\ChatbotConfigController;


use App\Http\Controllers\Client\ClientSettingsController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\AdminController;
// use App\Http\Controllers\UserController;
use App\Http\Controllers\CampaignController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\TemplateController;
// use App\Http\Controllers\ClientAuthController;
use App\Http\Controllers\Client\ClientDashboardController;
use App\Http\Controllers\Client\CampaignController as ClientCampaignController;
use App\Http\Controllers\Client\TemplateController as ClientTemplateController;
 

Route::get('/', function () {
    return redirect('/wa/login');
});



Route::prefix('wa')->group(function () {

    Route::get('login', [LoginController::class, 'showLoginForm'])
        ->name('login'); // 👈 FIX HERE

    Route::post('login', [LoginController::class, 'login'])
        ->name('wa.login.submit');

    Route::post('logout', [LoginController::class, 'logout'])
        ->name('wa.logout');
});

Route::prefix('wa')
    ->middleware(['admin.auth']) // ✅ sirf admin.auth — 'auth' hata do
    ->group(function () {

        // Dashboard
        Route::get('dashboard', [AdminController::class, 'dashboard'])
            ->name('wa.dashboard');

        // Route::resource('users', UserController::class);
        Route::resource('clients', ClientController::class);

        Route::get('campaigns/export/{id}', [CampaignController::class, 'export'])
            ->name('campaigns.export');
        Route::resource('campaigns', CampaignController::class);


        Route::get('settings', [SettingsController::class, 'index'])
            ->name('settings.index');

        Route::put('settings/app', [SettingsController::class, 'updateApp'])
            ->name('settings.app.update');

        Route::put('settings/profile', [SettingsController::class, 'updateProfile'])
            ->name('settings.profile.update');

        Route::put('settings/password', [SettingsController::class, 'updatePassword'])
            ->name('settings.password.update');

        Route::post('campaigns/{id}/send', [CampaignController::class, 'sendCampaign'])->name('campaigns.send');

        Route::get('templates/by-client/{clientId}', [TemplateController::class, 'getByClient'])->name('templates.by-client');

        Route::resource('templates', TemplateController::class);

        Route::post('campaigns/{campaign}/send-single/{contact}', [CampaignController::class, 'sendSingle'])
            ->name('campaigns.sendSingle');


   Route::get('/chatbot/history/{client_id}', [ChatbotHistoryController::class, 'index'])->name('admin.chatbot.history');
Route::get('/chatbot/history/{client_id}/{session_id}', [ChatbotHistoryController::class, 'show'])->name('admin.chatbot.history.show');

Route::get('/chatbot/history/{client_id}/{session_id}/download', [ChatbotHistoryController::class, 'downloadPdf'])
    ->name('admin.chatbot.history.download');

   //  Route::get('/chatbot-config/{client_id}', [ChatbotConfigController::class, 'edit'])->name('chatbot.config.edit');
   // Route::put('/chatbot-config/{client_id}', [ChatbotConfigController::class, 'update'])->name('chatbot.config.update');


    // Modal load: client ke saare prompts + KB fetch karo
    Route::get('/chatbot-config/{client_id}', [ChatbotConfigController::class, 'edit'])->name('chatbot.config.edit');
 
    // Prompts CRUD
    Route::post('/chatbot-config/{client_id}/prompts', [ChatbotConfigController::class, 'storePrompt'])->name('chatbot.config.prompts.store');
    Route::put('/chatbot-config/{client_id}/prompts/{prompt_id}', [ChatbotConfigController::class, 'updatePrompt'])->name('chatbot.config.prompts.update');
    Route::delete('/chatbot-config/{client_id}/prompts/{prompt_id}', [ChatbotConfigController::class, 'destroyPrompt'])->name('chatbot.config.prompts.destroy');
    Route::post('/chatbot-config/{client_id}/prompts/{prompt_id}/activate', [ChatbotConfigController::class, 'activatePrompt'])->name('chatbot.config.prompts.activate');
 
    // Knowledge Base CRUD
    Route::post('/chatbot-config/{client_id}/kb', [ChatbotConfigController::class, 'storeKb'])->name('chatbot.config.kb.store');
    Route::put('/chatbot-config/{client_id}/kb/{kb_id}', [ChatbotConfigController::class, 'updateKb'])->name('chatbot.config.kb.update');
    Route::delete('/chatbot-config/{client_id}/kb/{kb_id}', [ChatbotConfigController::class, 'destroyKb'])->name('chatbot.config.kb.destroy');
    Route::post('/chatbot-config/{client_id}/kb/{kb_id}/toggle', [ChatbotConfigController::class, 'toggleKb'])->name('chatbot.config.kb.toggle');

    Route::put('/chatbot-config/{client_id}/welcome-message', [ChatbotConfigController::class, 'updateWelcomeMessage'])->name('chatbot.config.welcome.update');
    });

Route::prefix('wa/client')
    ->middleware(['client.auth'])
    ->name('client.')
    ->group(function () {
        Route::get('dashboard', [ClientDashboardController::class, 'dashboard'])->name('dashboard');
        // Route::get('templates',           [ClientTemplateController::class, 'index'])->name('templates.index');
        // Route::get('templates/{id}',      [ClientTemplateController::class, 'show'])->name('templates.show');
        // Route::get('campaigns',           [ClientCampaignController::class, 'index'])->name('campaigns.index');
        // Route::get('campaigns/{id}',      [ClientCampaignController::class, 'show'])->name('campaigns.show');
        // Campaigns — full CRUD + send
     Route::middleware(['whatsapp.enabled'])->group(function () {

        Route::get('campaigns',                        [ClientCampaignController::class, 'index'])->name('campaigns.index');
        Route::get('campaigns/create',                 [ClientCampaignController::class, 'create'])->name('campaigns.create');
        Route::post('campaigns',                       [ClientCampaignController::class, 'store'])->name('campaigns.store');
        Route::get('campaigns/{id}',                   [ClientCampaignController::class, 'show'])->name('campaigns.show');
        Route::get('campaigns/{id}/edit',              [ClientCampaignController::class, 'edit'])->name('campaigns.edit');
        Route::put('campaigns/{id}',                   [ClientCampaignController::class, 'update'])->name('campaigns.update');
        Route::delete('campaigns/{id}',                [ClientCampaignController::class, 'destroy'])->name('campaigns.destroy');
        Route::get('campaigns/{id}/export',            [ClientCampaignController::class, 'export'])->name('campaigns.export');
        Route::post('campaigns/{id}/send',             [ClientCampaignController::class, 'sendCampaign'])->name('campaigns.send');
        Route::post('campaigns/{id}/send/{contactId}', [ClientCampaignController::class, 'sendSingle'])->name('campaigns.sendSingle');


         Route::get('templates',             [ClientTemplateController::class,   'index'])->name('templates.index');
        Route::get('templates/create',      [ClientTemplateController::class,   'create'])->name('templates.create');
        Route::post('templates',            [ClientTemplateController::class,   'store'])->name('templates.store');
        Route::get('templates/{id}/edit', [ClientTemplateController::class, 'edit'])->name('templates.edit');
        Route::put('templates/{id}', [ClientTemplateController::class, 'update'])->name('templates.update');
        Route::get('templates/{id}',        [ClientTemplateController::class,   'show'])->name('templates.show');
        Route::delete('templates/{id}',                [ClientTemplateController::class, 'destroy'])->name('templates.destroy');
        });


        // Settings
        Route::get('settings',          [ClientSettingsController::class, 'index'])->name('settings.index');
        Route::put('settings/profile',  [ClientSettingsController::class, 'updateProfile'])->name('settings.profile.update');
        Route::put('settings/password', [ClientSettingsController::class, 'updatePassword'])->name('settings.password.update');

       


Route::middleware(['chatbot.enabled'])->group(function () {
    Route::get('chatbot', [ChatbotHistoryController::class, 'clientIndex'])->name('chatbot.index');
    Route::get('chatbot/{session_id}', [ChatbotHistoryController::class, 'clientShow'])->name('chatbot.show');
    Route::get('chatbot/{session_id}/download', [ChatbotHistoryController::class, 'clientDownloadPdf'])->name('chatbot.download');

    // ✅ naye routes — Database Chatbot (Prompt + KB + Welcome Message)
    Route::get('chatbot-config', [ChatbotConfigController::class, 'clientEdit'])->name('chatbot.config.index');
    Route::put('chatbot-config/welcome-message', [ChatbotConfigController::class, 'clientUpdateWelcomeMessage'])->name('chatbot.config.welcome.update');
    Route::post('chatbot-config/prompts', [ChatbotConfigController::class, 'clientStorePrompt'])->name('chatbot.config.prompts.store');
    Route::put('chatbot-config/prompts/{prompt_id}', [ChatbotConfigController::class, 'clientUpdatePrompt'])->name('chatbot.config.prompts.update');
    Route::delete('chatbot-config/prompts/{prompt_id}', [ChatbotConfigController::class, 'clientDestroyPrompt'])->name('chatbot.config.prompts.destroy');
    Route::post('chatbot-config/prompts/{prompt_id}/activate', [ChatbotConfigController::class, 'clientActivatePrompt'])->name('chatbot.config.prompts.activate');
    Route::post('chatbot-config/kb', [ChatbotConfigController::class, 'clientStoreKb'])->name('chatbot.config.kb.store');
    Route::put('chatbot-config/kb/{kb_id}', [ChatbotConfigController::class, 'clientUpdateKb'])->name('chatbot.config.kb.update');
    Route::delete('chatbot-config/kb/{kb_id}', [ChatbotConfigController::class, 'clientDestroyKb'])->name('chatbot.config.kb.destroy');
    Route::post('chatbot-config/kb/{kb_id}/toggle', [ChatbotConfigController::class, 'clientToggleKb'])->name('chatbot.config.kb.toggle');
});
    });


