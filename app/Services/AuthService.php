<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Env;
use App\Core\Session;
use App\Mail\Mailer;
use App\Repositories\AdminRepository;
use App\Repositories\MerchantRepository;
use App\Repositories\PasswordResetRepository;
use App\Repositories\RiderProfileRepository;
use App\Repositories\RiderRepository;
use Throwable;

class AuthService
{
    public function __construct(
        private readonly MerchantRepository $merchants = new MerchantRepository(),
        private readonly RiderRepository $riders = new RiderRepository(),
        private readonly RiderProfileRepository $riderProfiles = new RiderProfileRepository(),
        private readonly AdminRepository $admins = new AdminRepository(),
        private readonly PasswordResetRepository $passwordResets = new PasswordResetRepository(),
        private readonly Mailer $mailer = new Mailer(),
    ) {}

    public function registerMerchant(array $data): int
    {
        return $this->merchants->create([
            'business_name' => trim($data['business_name']),
            'owner_name' => trim($data['owner_name']),
            'email' => strtolower(trim($data['email'])),
            'phone' => preg_replace('/\D+/', '', $data['phone']),
            'password_hash' => password_hash($data['password'], PASSWORD_BCRYPT),
            'city' => trim($data['city']),
        ]);
    }

    public function registerRider(array $data): int
    {
        $riderId = $this->riders->create([
            'name' => trim($data['name']),
            'email' => strtolower(trim($data['email'])),
            'phone' => preg_replace('/\D+/', '', $data['phone']),
            'password_hash' => password_hash($data['password'], PASSWORD_BCRYPT),
            'city' => trim($data['city']),
            'zone' => $data['zone'] ?: null,
            'wallet_provider' => $data['wallet_provider'],
            'bike_number' => $data['bike_number'] ?: null,
            'document_path' => null,
            'approval_status' => 'pending',
        ]);

        $this->riderProfiles->upsert($riderId, $data['wallet_provider'], preg_replace('/\D+/', '', $data['phone']), $data['zone'] ?: null);
        (new WalletService())->createWalletForRider($riderId);
        return $riderId;
    }

    public function attemptMerchantLogin(string $email, string $password): bool
    {
        $user = $this->merchants->findByEmail($email);
        if (!$user || !password_verify($password, $user['password_hash'])) {
            return false;
        }

        Session::start();
        Session::regenerate();
        Session::set('merchant_id', (int)$user['id']);
        return true;
    }

    public function attemptRiderLogin(string $email, string $password): bool
    {
        $user = $this->riders->findByEmail($email);
        if (!$user || !password_verify($password, $user['password_hash']) || $user['approval_status'] !== 'approved') {
            return false;
        }

        Session::start();
        Session::regenerate();
        Session::set('rider_id', (int)$user['id']);
        Session::set('rider_city', $user['city']);
        return true;
    }

    public function attemptAdminLogin(string $email, string $password): bool
    {
        $user = $this->admins->findByEmail($email);
        if (!$user || !password_verify($password, $user['password_hash'])) {
            return false;
        }

        Session::start();
        Session::regenerate();
        Session::set('admin_id', (int)$user['id']);
        return true;
    }

    public function sendPasswordReset(string $email): bool
    {
        $normalizedEmail = strtolower(trim($email));
        $userType = $this->resolveUserTypeByEmail($normalizedEmail);
        if ($userType === null) {
            return true;
        }

        $this->passwordResets->deleteExpired();

        $token = bin2hex(random_bytes(32));
        $expiresAt = date('Y-m-d H:i:s', strtotime('+60 minutes'));
        $this->passwordResets->create($userType, $normalizedEmail, $token, $expiresAt);

        $resetUrl = rtrim((string)Env::get('APP_URL', ''), '/') . '/reset-password/' . $token;
        if (str_starts_with($resetUrl, '/reset-password/')) {
            $host = $_SERVER['HTTP_HOST'] ?? '127.0.0.1:8000';
            $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
            $resetUrl = sprintf('%s://%s/reset-password/%s', $scheme, $host, $token);
        }

        return $this->sendResetEmail($normalizedEmail, $resetUrl, $expiresAt);
    }

    public function resetPassword(string $token, string $newPassword): bool
    {
        $reset = $this->passwordResets->findValidByToken($token);
        if (!$reset) {
            return false;
        }

        $passwordHash = password_hash($newPassword, PASSWORD_BCRYPT);
        match ($reset['user_type']) {
            'admin' => $this->admins->updatePasswordByEmail($reset['email'], $passwordHash),
            'rider' => $this->riders->updatePasswordByEmail($reset['email'], $passwordHash),
            default => $this->merchants->updatePasswordByEmail($reset['email'], $passwordHash),
        };

        $this->passwordResets->deleteByToken($token);
        return true;
    }

    private function resolveUserTypeByEmail(string $email): ?string
    {
        if ($this->admins->findByEmail($email)) {
            return 'admin';
        }

        if ($this->merchants->findByEmail($email)) {
            return 'merchant';
        }

        if ($this->riders->findByEmail($email)) {
            return 'rider';
        }

        return null;
    }

    private function sendResetEmail(string $email, string $resetUrl, string $expiresAt): bool
    {
        try {
            $mail = $this->mailer->make();
            $mail->setFrom((string)Env::get('MAIL_FROM_ADDRESS', 'no-reply@movaja.com'), (string)Env::get('MAIL_FROM_NAME', 'MovaJá'));
            $mail->addAddress($email);
            $mail->isHTML(true);
            $mail->Subject = 'Recuperação de senha - MovaJá';
            $mail->Body = sprintf(
                '<p>Recebemos um pedido para redefinir sua senha.</p><p><a href="%s">Clique aqui para redefinir a senha</a></p><p>Este link expira em %s.</p>',
                htmlspecialchars($resetUrl, ENT_QUOTES),
                htmlspecialchars($expiresAt, ENT_QUOTES)
            );
            $mail->AltBody = "Use este link para redefinir sua senha: {$resetUrl}. Expira em {$expiresAt}.";
            return $mail->send();
        } catch (Throwable $e) {
            error_log('[password-reset-email] ' . $e->getMessage());
            return false;
        }
    }
}
