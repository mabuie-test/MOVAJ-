<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Services\AuthService;

class AuthController extends Controller
{
    public function __construct(private readonly AuthService $auth = new AuthService()) {}

    public function loginForm(Request $request): void { $this->view('auth/login'); }
    public function registerMerchantForm(Request $request): void { $this->view('auth/register_merchant'); }
    public function registerRiderForm(Request $request): void { $this->view('auth/register_rider'); }
    public function forgotPasswordForm(Request $request): void { $this->view('auth/forgot_password'); }

    public function login(Request $request): void
    {
        $role = (string)$request->input('role', 'merchant');
        $email = (string)$request->input('email');
        $password = (string)$request->input('password');

        $ok = match ($role) {
            'admin' => $this->auth->attemptAdminLogin($email, $password),
            'rider' => $this->auth->attemptRiderLogin($email, $password),
            default => $this->auth->attemptMerchantLogin($email, $password),
        };

        if (!$ok) {
            $this->view('auth/login', ['error' => 'Credenciais inválidas ou conta sem aprovação.']);
            return;
        }

        Response::redirect(match ($role) {
            'admin' => '/admin',
            'rider' => '/rider/dashboard',
            default => '/merchant/dashboard',
        });
    }

    public function registerMerchant(Request $request): void
    {
        $this->auth->registerMerchant($request->all());
        Response::redirect('/login');
    }

    public function registerRider(Request $request): void
    {
        $this->auth->registerRider($request->all());
        Response::redirect('/login');
    }

    public function forgotPassword(Request $request): void { Response::json(['message' => 'Link de recuperação enviado (fluxo pronto para Mailer).']); }
    public function resetPasswordForm(Request $request, string $token): void { $this->view('auth/reset_password', ['token' => $token]); }
    public function resetPassword(Request $request): void { Response::json(['message' => 'Senha redefinida.']); }
}
