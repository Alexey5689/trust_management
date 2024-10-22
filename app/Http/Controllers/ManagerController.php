<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Support\Facades\Auth;
class ManagerController extends Controller
{
    public function showClients():Response
    {

         // Получаем его роль
        $user = Auth::user();
        $clients = $user->managedUsers->load('userContracts')->map(function ($client) {
            return [
                'id' => $client->id,
                'first_name' => $client->first_name,
                'last_name' => $client->last_name,
                'email' => $client->email,
                'phone_number' => $client->phone_number,
                'contracts' => $client->userContracts ? $client->userContracts->toArray() : [], // Загружаем контракты
            ];
        });
        $role = $user->role->title;
        return Inertia::render('Clients', [
                'clients' => $clients,
                'role' => $role,
        ]);
    }
}
