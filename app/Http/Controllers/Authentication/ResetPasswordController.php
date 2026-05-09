<?php

namespace App\Http\Controllers\Authentication;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Auth;

class ResetPasswordController extends Controller
{
    public function create(Request $request, $token)
    {
        return inertia('Authentication/ResetPassword', [
          'token' => $token,
          'email' => $request->email,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
        'token' => ['required'],
        'email' => ['required', 'email'],
        'password' => ['required', 'min:8'],
        ]);

        $status = Password::reset(
          $request->only('email', 'password', 'token'),
          function ($user) use ($request) {
              $user->forceFill([
              'password' => bcrypt($request->password),
              ])->save();
              Auth::login($user);
          }
        );

        if ($status == Password::PASSWORD_RESET) {
          return redirect('/')->with('status', __($status));
        }

        return back()->withErrors(['email' => __($status)]);
    }
}
