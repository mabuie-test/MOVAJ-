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
        $email = strtolower(trim((string)($data['email'] ?? '')));
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new \InvalidArgumentException('Email inválido para cadastro de rider.');
        }

        $documents = $this->storeRiderDocuments();

        $riderId = $this->riders->create([
            'name' => trim((string)$data['name']),
            'email' => $email,
            'phone' => preg_replace('/\D+/', '', (string)$data['phone']),
            'password_hash' => password_hash((string)$data['password'], PASSWORD_BCRYPT),
            'city' => trim((string)$data['city']),
            'zone' => $data['zone'] ?: null,
            'wallet_provider' => (string)$data['wallet_provider'],
            'bike_number' => $data['bike_number'] ?: null,
            'document_path' => json_encode($documents, JSON_UNESCAPED_UNICODE),
            'approval_status' => 'pending',
            'id_number' => $data['id_number'] ?: null,
            'id_issue_date' => $data['id_issue_date'] ?: null,
            'id_expiry_date' => $data['id_expiry_date'] ?: null,
            'nuit' => $data['nuit'] ?: null,
            'address_line' => $data['address_line'] ?: null,
            'emergency_contact_phone' => isset($data['emergency_contact_phone']) ? preg_replace('/\D+/', '', (string)$data['emergency_contact_phone']) : null,
            'bi_front_path' => $documents['bi_front_path'] ?? null,
            'bi_back_path' => $documents['bi_back_path'] ?? null,
            'selfie_path' => $documents['selfie_path'] ?? null,
            'motorcycle_plate' => $data['motorcycle_plate'] ?: null,
            'motorcycle_livrete' => $data['motorcycle_livrete'] ?: null,
            'motorcycle_model' => $data['motorcycle_model'] ?: null,
            'motorcycle_year' => !empty($data['motorcycle_year']) ? (int)$data['motorcycle_year'] : null,
            'motorcycle_front_path' => $documents['motorcycle_front_path'] ?? null,
            'motorcycle_back_path' => $documents['motorcycle_back_path'] ?? null,
        ]);

        $this->riderProfiles->upsert($riderId, (string)$data['wallet_provider'], preg_replace('/\D+/', '', (string)$data['phone']), $data['zone'] ?: null);
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

    private function storeRiderDocuments(): array
    {
        $files = [
            'bi_front' => 'bi_front_path',
            'bi_back' => 'bi_back_path',
            'selfie_photo' => 'selfie_path',
            'motorcycle_front' => 'motorcycle_front_path',
            'motorcycle_back' => 'motorcycle_back_path',
        ];

        $saved = [];
        $baseDir = __DIR__ . '/../../storage/uploads/riders';
        if (!is_dir($baseDir)) {
            mkdir($baseDir, 0775, true);
        }

        foreach ($files as $input => $targetKey) {
            if (empty($_FILES[$input]['tmp_name'])) {
                continue;
            }

            $tmp = (string)$_FILES[$input]['tmp_name'];
            if (!is_uploaded_file($tmp)) {
                continue;
            }

            $ext = strtolower(pathinfo((string)$_FILES[$input]['name'], PATHINFO_EXTENSION));
            $safeExt = in_array($ext, ['jpg', 'jpeg', 'png', 'webp'], true) ? $ext : 'jpg';
            $filename = sprintf('%s_%s.%s', $input, bin2hex(random_bytes(6)), $safeExt);
            $absolutePath = $baseDir . '/' . $filename;

            if (!move_uploaded_file($tmp, $absolutePath)) {
                throw new \RuntimeException('Falha ao salvar documentos do rider.');
            }

            $saved[$targetKey] = 'storage/uploads/riders/' . $filename;
        }

        return $saved;
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
