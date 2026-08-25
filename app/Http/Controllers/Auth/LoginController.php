<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class LoginController extends Controller
{
    /**
     * Muestra el formulario de inicio de sesión (Español Latino).
     */
    public function showLoginForm(): View
    {
        if (Auth::check()) {
            return redirect()->route('admin.dashboard');
        }

        return view('auth.login');
    }

    /**
     * Procesa el inicio de sesión.
     */
    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ], [
            'email.required' => 'Por favor, ingresa tu correo electrónico.',
            'email.email' => 'El formato del correo electrónico no es válido.',
            'password.required' => 'Por favor, ingresa tu contraseña.',
        ]);

        $remember = $request->boolean('remember');

        if (Auth::attempt(['email' => $credentials['email'], 'password' => $credentials['password'], 'is_active' => true], $remember)) {
            $request->session()->regenerate();

            $user = Auth::user();
            $rolNombre = $user->role === 'admin' ? 'Administrador' : 'Personal de Atención';

            return redirect()->intended(route('admin.dashboard'))
                ->with('success', '¡Bienvenido/a de nuevo, ' . $user->name . '! (' . $rolNombre . ')');
        }

        // Si falló, verificar si el usuario existe pero está inactivo
        $userExists = \App\Models\User::where('email', $credentials['email'])->first();
        if ($userExists && !$userExists->is_active) {
            throw ValidationException::withMessages([
                'email' => 'Tu cuenta está desactivada. Por favor, comunícate con el administrador.',
            ]);
        }

        throw ValidationException::withMessages([
            'email' => 'Las credenciales proporcionadas no coinciden con nuestros registros.',
        ]);
    }

    /**
     * Cierra la sesión del usuario.
     */
    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/')->with('info', 'Has cerrado sesión correctamente.');
    }
}
