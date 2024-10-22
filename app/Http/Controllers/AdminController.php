<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Support\Facades\Auth;

class AdminController extends Controller
{
    public function showClients()
    {
        $user = Auth::user(); // Получаем текущего пользователя
        $role = $user->role->title; // Получаем его роль

        // Фильтрация клиентов
        $clients = User::whereHas('role', function($query) {
            $query->where('title', 'client'); // Фильтрация по роли 'client'
        })->with('userContracts')->get();
        // dd($clients);
        return Inertia::render('Clients', [
            'clients' => $clients,
            'role' => $role, // Передаем роль пользователя в Vue-компонент
        ]);
    }

    public function showManagers()
    {
        $user = Auth::user(); // Получаем текущего пользователя
        $role = $user->role->title; // Получаем его роль
        // dd($user, $role);
        // Фильтрация менеджеров
        $managers = User::whereHas('role', function($query) {
            $query->where('title', 'manager');
        })->with('managerContracts')->get();

        return Inertia::render('Managers', [
            'managers' => $managers,
            'role' => $role, // Передаем роль пользователя в Vue-компонент
        ]);
    }
}
