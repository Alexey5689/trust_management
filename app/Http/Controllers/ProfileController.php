<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
class ProfileController extends Controller
{
    public function create (){
        $user = Auth::user();
        return Inertia::render('Profile', [
            'user' => $user
        ]);
    }
}
