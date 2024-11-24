<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Log;
use Illuminate\Support\Facades\Auth;
use App\Models\Contract;
use Illuminate\Http\RedirectResponse;

class DeleteController extends Controller
{
       
      // Удаление user
      public function deleteUser(User $user): RedirectResponse
      {
          // dd($user);
        //   Log::create([
        //     'model_id' => $user->id,
        //     'model_type' => User::class,
        //     'change' => "Удаление",
        //     'action' => 'delete',
        //     'old_value' => null,
        //     'new_value' => null,
        //     'created_by' => Auth::id(),
        // ]);
            //$user->delete();
            return redirect('/')->with('success', 'User удален');
      }
      public function deleteContract(Contract $contract): RedirectResponse
      {
          //dd($contract);
        //   Log::create([
        //     'model_id' => $contract->user_id,
        //     'model_type' => Contract::class,
        //     'change' => "Удаление договора",
        //     'action' => 'delete',
        //     'old_value' => 'Договор No ' . $contract->contract_number,
        //     'new_value' => null,
        //     'created_by' => Auth::id(),
        // ]);
          $contract->delete();
          return redirect(route('admin.contracts'))->with('success', 'Контракт удален');
      }
}
