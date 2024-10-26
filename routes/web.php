<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Auth;


Route::get('/', function () {
    if (!Auth::check()) {
        return redirect()->route('login');  // Если пользователь не авторизован
    }

    $user = Auth::user();

    if ($user->role->title === 'admin') {
        return redirect()->route('admin.profile');
    } elseif ($user->role->title === 'manager') {
        return redirect()->route('manager.profile');
    } else {
        return redirect()->route('client.profile');
    }
});


Route::middleware('auth')->group(function () {
    Route::get('profile/edit',[ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('profile//edit', [ProfileController::class, 'update'])->name('profile.update');
    Route::get('password/edit', [ProfileController::class, 'editPassword'])->name('password.edit');
    Route::patch('password/edit', [ProfileController::class, 'updatePassword'])->name('password.update');
    Route::get('email/edit', [ProfileController::class, 'editEmail'])->name('email.edit');
    Route::patch('email/edit', [ProfileController::class, 'updateEmail'])->name('email.update');
});


require __DIR__ . '/auth.php';




// Route::get('/success-registration', function () {
//     return Inertia::render('Messages/SuccessRegistration');
// })->name('success-registration');








// Route::middleware('auth')->group(function () {

//     Route::prefix('admin')->group(function () {
//         // Route::get('/',[DashboardController::class, 'create'])->name('admin.dashboard');
//         Route::get('/profile', [ProfileController::class, 'create'])->name('admin.profile');
//         Route::get('/clients',[AdminController::class, 'showClients'])->name('admin.clients');
//         Route::get('/managers',[AdminController::class, 'showManagers'])->name('admin.managers');
//         Route::get('/contracts',[AdminController::class, 'showAllContracts'])->name('admin.contracts');
//         //рег менеджер
//         Route::get('/registration-manager', [AdminController::class, 'createManagersByAdmin'])->name('admin.registration.manager');
//         Route::post('/registration-manager', [AdminController::class, 'storeManagersByAdmin']);
//         //рег клиент
//         Route::get('/registration-client', [AdminController::class, 'createClientsByAdmin'])->name('admin.registration.client');
//         Route::post('/registration-client', [AdminController::class, 'storeClientsByAdmin']);
//     });

//     Route::prefix('manager')->group(function () {
//         // Route::get('/',[DashboardController::class, 'create'])->name('manager.dashboard');
//         Route::get('/profile', [ProfileController::class, 'create'])->name('manager.profile');
//         Route::get('/clients',[ManagerController::class, 'showClients'])->name('manager.clients');
//         Route::get('/contracts', [ManagerController::class, 'showContracts'])->name('manager.contracts');
//         // //рег клиент
//         Route::get('/registration-client', [ManagerController::class, 'createClientsByManager'])->name('manager.registration.client');
//         Route::post('/registration-client', [ManagerController::class, 'storeClientsByManager']);
//     });

//     Route::prefix('client')->group(function () {
//         // Route::get('/',[DashboardController::class, 'create'])->name('client.dashboard');
//         Route::get('/profile', [ProfileController::class, 'create'])->name('client.profile');
//         // Route::get('/contracts', [ClientController::class, 'showContracts'])->name('contracts');

//         // Route::get('/edit',[ProfileController::class, 'edit'])->name('profile.edit');
//         // Route::patch('/edit', [ProfileController::class, 'update'])->name('profile.update');
//         // //
//         // Route::get('/clients', [ClientController::class, 'showClients'])->name('client.clients');
//         // Route::get('/managers', [ClientController::class, 'showManagers'])->name('client.managers');
//     });

//     Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])
//     ->name('logout');

// });
