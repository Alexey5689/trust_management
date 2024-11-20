<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\ProfileController;

Route::middleware('auth' , 'role:client')->group(function () {
    Route::prefix('client')->group(function () {
        Route::get('/profile', [ProfileController::class, 'createProfile'])->name('client.profile');
        Route::get('/contracts', [ClientController::class, 'showContracts'])->name('client.contracts');
        Route::get('/applications', [ClientController::class, 'showApplications'])->name('client.applications');
        Route::get ('/balance-transactions', [ClientController::class, 'showBalanceTransactions'])->name('client.balance.ransactions');
    });
});