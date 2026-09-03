<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\ResetPasswordMail;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PasswordResetController extends Controller
{
    /**
     * Muestra la vista para solicitar el enlace de restablecimiento.
     */
    public function showLinkRequestForm(): View
    {
        return view('auth.forgot-password');
    }

    /**
     * Procesa la solicitud y envía el correo con el enlace de restablecimiento.
     */
    public function sendResetLinkEmail(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => 'required|email',
        ], [
            'email.required' => 'Por favor, ingresa tu correo electrónico.',
            'email.email' => 'El formato del correo electrónico no es válido.',
        ]);

        $user = User::where('email', $request->email)->first();

        if ($user && $user->is_active) {
            try {
                $token = Password::broker()->createToken($user);
                Mail::to($user->email)->send(new ResetPasswordMail($user, $token));
            } catch (\Throwable $e) {
                Log::error('Error enviando correo de restablecimiento: ' . $e->getMessage());
                return back()->with('error', 'Ocurrió un error al enviar el correo. Por favor, intenta de nuevo más tarde.');
            }
        }

        // Por seguridad, confirmamos el envío tanto si el usuario existe como si no
        return back()->with('status', 'Si el correo está registrado en el sistema, te hemos enviado un enlace para restablecer tu contraseña. Revisa tu bandeja de entrada o spam.');
    }

    /**
     * Muestra el formulario para ingresar la nueva contraseña.
     */
    public function showResetForm(Request $request, string $token): View
    {
        return view('auth.reset-password', [
            'token' => $token,
            'email' => $request->query('email')
        ]);
    }

    /**
     * Actualiza la contraseña del usuario tras validar el token.
     */
    public function reset(Request $request): RedirectResponse
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|confirmed|min:6',
        ], [
            'token.required' => 'El token de seguridad es requerido.',
            'email.required' => 'El correo electrónico es obligatorio.',
            'email.email' => 'Ingresa un correo electrónico válido.',
            'password.required' => 'Debes ingresar una nueva contraseña.',
            'password.min' => 'La contraseña debe tener al menos 6 caracteres.',
            'password.confirmed' => 'La confirmación de la contraseña no coincide.',
        ]);

        $status = Password::broker()->reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password) {
                $user->password = Hash::make($password);
                $user->setRememberToken(Str::random(60));
                $user->save();

                event(new PasswordReset($user));
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return redirect()->route('login')
                ->with('success', '¡Tu contraseña ha sido restablecida exitosamente! Ya puedes iniciar sesión.');
        }

        return back()
            ->withInput($request->only('email'))
            ->withErrors(['email' => 'El enlace para restablecer la contraseña no es válido o ya ha expirado.']);
    }
}
