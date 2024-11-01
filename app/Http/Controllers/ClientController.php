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
        $role = $user->role->title;
         /** @var User $user */
        $contracts = $user->userContracts()->with('user')->get();
        return Inertia::render('Contracts', [
            'role' => $role,
            'contracts' => $contracts
        ]);
    }
}
