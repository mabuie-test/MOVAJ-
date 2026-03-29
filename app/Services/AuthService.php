<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Session;
use App\Repositories\AdminRepository;
use App\Repositories\MerchantRepository;
use App\Repositories\RiderProfileRepository;
use App\Repositories\RiderRepository;

class AuthService
{
    public function __construct(
        private readonly MerchantRepository $merchants = new MerchantRepository(),
        private readonly RiderRepository $riders = new RiderRepository(),
        private readonly RiderProfileRepository $riderProfiles = new RiderProfileRepository(),
        private readonly AdminRepository $admins = new AdminRepository()
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
}
