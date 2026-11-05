<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\CompanyDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'whatsapp' => 'required',
            'password' => 'required',
        ]);

        $user = User::where('whatsapp', $credentials['whatsapp'])->first();

        if ($user && Hash::check($credentials['password'], $user->password)) {
            if ($user->status !== 'ativo') {
                return back()->withErrors(['whatsapp' => 'Conta inativa ou suspensa. Contate o suporte.']);
            }

            Auth::login($user, $request->filled('remember'));
            $request->session()->regenerate();

            return redirect()->intended(route('dashboard'));
        }

        return back()->withErrors([
            'whatsapp' => 'As credenciais informadas não correspondem aos nossos registros.',
        ])->onlyInput('whatsapp');
    }

    public function showRegister()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'whatsapp' => 'required|string|max:30|unique:users,whatsapp',
            'email' => 'nullable|email|max:255|unique:users,email',
            'password' => 'required|string|min:6|confirmed',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'whatsapp' => $validated['whatsapp'],
            'email' => $validated['email'] ?? null,
            'password' => Hash::make($validated['password']),
            'role_id' => 4,
            'status' => 'ativo',
            'limite' => 100,
            'creditos' => 10,
            'trial_ends_at' => now()->addDays(7),
        ]);

        CompanyDetail::create([
            'user_id' => $user->id,
            'company_name' => 'Revenda de ' . $user->name,
            'company_whatsapp' => $user->whatsapp,
        ]);

        Auth::login($user);
        return redirect()->route('dashboard')->with('success', 'Bem-vindo ao Gestor! Sua conta foi criada com sucesso.');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
