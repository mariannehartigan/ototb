<?php

namespace App\Http\Controllers\Authentication;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
  public function create() {
    if (auth()->check()) {
      return redirect('/');
    }
    return inertia ('Authentication/Login');
  }

  public function store(Request $request) {
    if (!Auth::attempt($request->validate([
      'email' => 'required|string|email',
      'password' => 'required|string'
    ]), true)) {
      throw ValidationException::withMessages([
        'email' => 'Incorrect username or password'
      ]);
    }

    $request->session()->regenerate();

    return redirect('/');
  }

  public function destroy() {
    //
  }
}
