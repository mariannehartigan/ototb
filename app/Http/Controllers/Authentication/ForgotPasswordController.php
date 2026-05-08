<?php

namespace App\Http\Controllers\Authentication;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;

class ForgotPasswordController extends Controller
{
  public function create()
  {
    return inertia('Authentication/ForgotPassword');
  }

  public function store(Request $request)
  {
    $request->validate([
      'email' => ['required', 'email'],
    ]);

    $status = Password::sendResetLink(
      $request->only('email')
    );

    return inertia('Authentication/PasswordResetLinkSent', [
      'email' => $request->email,
    ]);
  }
}
