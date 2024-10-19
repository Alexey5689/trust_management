<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AdminController extends Controller
{
    public function showClients(){
        $clients = User::whereHas('role', function($query) {
            $query->where('title', 'client'); // Фильтрация по роли 'client'
        })->with('userContracts')->get();
        return Inertia::render('Clients', [
            'clients' => $clients,
        ]);
    }

    public function showManagers(){
        $managers = User::whereHas('role', function($query) {
            $query->where('title', 'manager');
        })->with('managerContracts')->get();
        return Inertia::render('Managers', [
            'managers' => $managers,
        ]);
    }

}
// $manager = Auth::user()->load(['managedUsers.userContracts']);

//         // Преобразуем коллекцию клиентов и их контрактов в массив для отправки во Vue компонент
//         $clients = $manager->managedUsers->map(function ($client) {
//             return [
//                 'id' => $client->id,
//                 'first_name' => $client->first_name,
//                 'last_name' => $client->last_name,
//                 'email' => $client->email,
//                 'phone_number' => $client->phone_number,
//                 'contracts' => $client->userContracts->toArray(), // Загружаем контракты
//             ];
//         });
