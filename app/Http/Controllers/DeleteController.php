<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Contract;
use Illuminate\Http\RedirectResponse;

class DeleteController extends Controller
{
       
      // Удаление user
      public function deleteUser(User $user): RedirectResponse
      {
          // dd($user);
            $user->delete();
            return redirect('/')->with('success', 'User удален');
      }
      public function deleteContract(Contract $contract): RedirectResponse
      {
          //dd($contract);
          $contract->delete();
          return redirect(route('admin.contracts'))->with('success', 'Контракт удален');
      }
}
