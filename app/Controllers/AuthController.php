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

    public function loginForm(Request $request): void
    {
        $notice = $request->input('notice');
        $this->view('auth/login', ['notice' => $notice]);
    }

    public function registerMerchantForm(Request $request): void { $this->view('auth/register_merchant'); }
    public function registerRiderForm(Request $request): void { $this->view('auth/register_rider'); }

    public function forgotPasswordForm(Request $request): void
    {
        $status = (string)$request->input('status', '');
        $message = $status === 'sent'
            ? 'Se o email estiver registado, enviamos um link de recuperação. Verifique sua caixa de entrada e spam.'
            : null;
        $error = $status === 'mail_error'
            ? 'Não foi possível enviar o email agora. Tente novamente em instantes.'
            : null;

        $this->view('auth/forgot_password', ['message' => $message, 'error' => $error]);
    }

    public function login(Request $request): void
    {
        $role = (string)$request->input('role', 'merchant');
        $email = (string)$request->input('email');
        $password = (string)$request->input('password');

        if ($this->auth->attemptAdminLogin($email, $password)) {
            Response::redirect('/admin');
            return;
        }

        $ok = $role === 'rider'
            ? $this->auth->attemptRiderLogin($email, $password)
            : $this->auth->attemptMerchantLogin($email, $password);

        if (!$ok) {
            $this->view('auth/login', ['error' => 'Credenciais inválidas ou conta sem aprovação.']);
            return;
        }

        Response::redirect($role === 'rider' ? '/rider/dashboard' : '/merchant/dashboard');
    }

    public function registerMerchant(Request $request): void
    {
        $this->auth->registerMerchant($request->all());
        Response::redirect('/login');
    }

    public function registerRider(Request $request): void
    {
        try {
            $this->auth->registerRider($request->all());
            Response::redirect('/login?notice=Conta rider criada. Aguarde aprovação do admin.');
        } catch (\Throwable $e) {
            $this->view('auth/register_rider', ['error' => $e->getMessage()]);
        }
    }

    public function forgotPassword(Request $request): void
    {
        $email = (string)$request->input('email', '');
        $sent = $this->auth->sendPasswordReset($email);
        Response::redirect($sent ? '/forgot-password?status=sent' : '/forgot-password?status=mail_error');
    }

    public function resetPasswordForm(Request $request, string $token): void
    {
        $this->view('auth/reset_password', ['token' => $token]);
    }

    public function resetPassword(Request $request): void
    {
        $token = (string)$request->input('token', '');
        $password = (string)$request->input('password', '');
        $passwordConfirmation = (string)$request->input('password_confirmation', '');

        if ($password === '' || strlen($password) < 4) {
            $this->view('auth/reset_password', ['token' => $token, 'error' => 'A senha deve ter pelo menos 4 caracteres.']);
            return;
        }

        if ($password !== $passwordConfirmation) {
            $this->view('auth/reset_password', ['token' => $token, 'error' => 'A confirmação de senha não coincide.']);
            return;
        }

        if (!$this->auth->resetPassword($token, $password)) {
            $this->view('auth/reset_password', ['token' => $token, 'error' => 'Token inválido ou expirado. Solicite um novo link.']);
            return;
        }

        Response::redirect('/login?notice=Senha redefinida com sucesso. Faça login com a nova senha.');
    }
}
