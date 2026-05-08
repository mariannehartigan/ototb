<?php

namespace App\Http\Controllers\Authentication;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use App\Models\User;

class CreateAccountController extends Controller
{
	public function create()
	{
		return inertia('Authentication/CreateAccount');
	}

	public function store(Request $request)
	{
		$user = User::make($request->validate([
      'name' => 'required',
      'email' => 'required|email|unique:users',
      'password' => 'required|min:8'
    ]));
    $user->save();
    Auth::login($user);

    return redirect('/');
	}
}
