<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
class DashboardController extends Controller
{
    public function create (){
        $user = Auth::user();
        $role = [
            'role_id' => $user->role_id,
        ];
        return Inertia::render('Dashboard', [
            'user' => $role
        ]);
    }
}
