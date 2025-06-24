<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        // Redirect users to their appropriate dashboard based on role
        $user = auth()->user();
        
        switch ($user->role) {
            case 'admin':
                return redirect()->route('admin.dashboard');
            case 'rescuer':
                return redirect()->route('rescuer.dashboard');
            case 'victim':
                return redirect()->route('victim.dashboard');
            default:
                return view('home');
        }
    }
}
