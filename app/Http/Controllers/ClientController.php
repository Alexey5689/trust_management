<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Support\Facades\Auth;

class ClientController extends Controller
{
    public function showContracts(){
        $user = Auth::user();
        $contracts = $user->userContracts->toArray();
        return Inertia::render('Contracts', [
            'contracts' => $contracts
        ]);
    }
}
