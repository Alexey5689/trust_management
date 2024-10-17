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
        $manager = Auth::user()->load(['managedUsers.userContracts']);

        // Преобразуем коллекцию клиентов и их контрактов в массив для отправки во Vue компонент
        $clients = $manager->managedUsers->map(function ($client) {
            return [
                'id' => $client->id,
                'first_name' => $client->first_name,
                'last_name' => $client->last_name,
                'email' => $client->email,
                'phone_number' => $client->phone_number,
                'contracts' => $client->userContracts->toArray(), // Загружаем контракты
            ];
        });
        return Inertia::render('CLientManager', [
                'clients' => $clients
        ]);
    }
}
