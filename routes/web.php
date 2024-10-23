<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;





Route::get('/', function () {
    return redirect()->route('login');  // Перенаправляем на маршрут login
});

Route::get('/success-registration', function (){
    return Inertia::render('Messages/SuccessRegistration');
})->name('success-registration');

require __DIR__.'/auth.php';
