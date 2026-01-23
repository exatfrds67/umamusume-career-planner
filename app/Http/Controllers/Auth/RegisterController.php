<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\View\View;

class RegisterController extends Controller
{
    /**
     * Display the registration form.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     */
    public function store(RegisterRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        // Ensure password is a string
        $password = $validated['password'];
        if (! is_string($password)) {
            throw new \InvalidArgumentException('Password must be a string');
        }

        $user = User::create([
            'uuid' => Str::uuid()->toString(),
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($password),
            'preferences' => [],
            'accessibility_settings' => [],
            'ai_settings' => ['subscription_tier' => 'free', 'budget_limit' => 10.0],
            'mcp_settings' => [],
        ]);

        event(new Registered($user));

        Auth::login($user);

        return redirect()->route('dashboard')->with('success', 'Welcome to Umamusume Career Planner!');
    }
}
