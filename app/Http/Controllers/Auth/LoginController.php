<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;

class LoginController extends Controller
{
    use AuthenticatesUsers;

    // No need for $redirectTo property anymore

    public function __construct()
    {
        $this->middleware('guest')->except('logout');
        $this->middleware('auth')->only('logout');
    }

    // Role-based redirection after login
    protected function authenticated(Request $request, $user)
    {
        if ($user->role == 'victim') {
            return redirect('/victim');
        } elseif ($user->role == 'rescuer') {
            return redirect('/rescuer');
        } elseif ($user->role == 'admin') {
            return redirect('/admin');
        }

        // default fallback
        return redirect('/home');
    }
}
