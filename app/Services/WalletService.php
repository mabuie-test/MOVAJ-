<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Env;
use App\Repositories\OrderRepository;
use App\Repositories\RiderProfileRepository;
use App\Repositories\WalletRepository;

class WalletService
{
    public function __construct(
        private readonly WalletRepository $wallets = new WalletRepository(),
        private readonly OrderRepository $orders = new OrderRepository(),
        private readonly RiderProfileRepository $profiles = new RiderProfileRepository(),
        private readonly PaymentService $payment = new PaymentService(),
    ) {}

    public function createWalletForRider(int $riderId): void
    {
        $this->wallets->createWallet($riderId);
    }

    public function creditRiderWalletFromOrder(int $orderId): bool
    {
        $order = $this->orders->findById($orderId);
        if (!$order || empty($order['assigned_rider_id'])) return false;

        $this->createWalletForRider((int)$order['assigned_rider_id']);
        $wallet = $this->wallets->walletByRider((int)$order['assigned_rider_id']);
        if (!$wallet) return false;

        $before = (float)$wallet['available_balance'];
        $amount = (float)$order['rider_payout'];
        $after = $before + $amount;

        $this->wallets->updateBalances((int)$wallet['id'], $after, (float)$wallet['pending_balance'], (float)$wallet['total_credited'] + $amount, (float)$wallet['total_paid_out']);
        $this->wallets->addTransaction([
            'rider_wallet_id' => $wallet['id'],
            'order_id' => $orderId,
            'type' => 'credit',
            'amount' => $amount,
            'balance_before' => $before,
            'balance_after' => $after,
            'reference' => 'order-credit-' . $orderId,
            'notes' => 'Crédito por entrega concluída',
        ]);

        if (filter_var(Env::get('AUTO_PAYOUT_ON_DELIVERY', true), FILTER_VALIDATE_BOOL)) {
            $this->requestPayout((int)$order['assigned_rider_id'], 'auto');
        }
        return true;
    }

    public function getAvailableBalance(int $riderId): float
    {
        $wallet = $this->wallets->walletByRider($riderId);
        return (float)($wallet['available_balance'] ?? 0);
    }

    public function getPendingBalance(int $riderId): float
    {
        $wallet = $this->wallets->walletByRider($riderId);
        return (float)($wallet['pending_balance'] ?? 0);
    }

    public function requestPayout(int $riderId, string $provider): bool
    {
        $wallet = $this->wallets->walletByRider($riderId);
        $profile = $this->profiles->findByRiderId($riderId);
        if (!$wallet || !$profile) return false;

        $amount = (float)$wallet['available_balance'];
        if ($amount < (float)Env::get('MIN_PAYOUT_AMOUNT', 50)) {
            return false;
        }

        $requestId = $this->wallets->createPayoutRequest([
            'rider_wallet_id' => $wallet['id'],
            'provider' => $provider === 'auto' ? $profile['wallet_provider'] : $provider,
            'phone' => $profile['wallet_number'],
            'amount' => $amount,
            'status' => 'pending',
            'debito_reference' => null,
            'raw_response' => null,
        ]);

        return $this->processWalletPayout($requestId);
    }

    public function processWalletPayout(int $walletPayoutId): bool
    {
        $pending = array_values(array_filter($this->wallets->pendingPayoutRequests(), fn($p) => (int)$p['id'] === $walletPayoutId));
        if (empty($pending)) return false;
        $request = $pending[0];

        $payload = [
            'external_reference' => 'wallet-payout-' . $request['id'],
            'customer_msisdn' => $this->payment->normalizePhone($request['phone']),
            'amount' => (float)$request['amount'],
            'description' => 'Wallet payout request #' . $request['id'],
        ];

        $response = $request['provider'] === 'emola' ? $this->payment->initiateRiderEmolaPayout($payload) : $this->payment->initiateRiderMpesaPayout($payload);
        $status = ($response['status'] ?? '') === 'success' ? 'completed' : 'failed';
        $this->wallets->updatePayoutRequest((int)$request['id'], $status, $response['reference'] ?? null, json_encode($response, JSON_THROW_ON_ERROR));

        if ($status === 'completed') {
            $walletId = (int)$request['rider_wallet_id'];
            $db = \App\Core\Database::connection();
            $wallet = $db->query('SELECT * FROM rider_wallets WHERE id=' . $walletId)->fetch();
            $before = (float)$wallet['available_balance'];
            $after = max(0, $before - (float)$request['amount']);
            $this->wallets->updateBalances($walletId, $after, (float)$wallet['pending_balance'], (float)$wallet['total_credited'], (float)$wallet['total_paid_out'] + (float)$request['amount']);
            $this->wallets->addTransaction([
                'rider_wallet_id' => $walletId,
                'order_id' => null,
                'type' => 'payout',
                'amount' => (float)$request['amount'],
                'balance_before' => $before,
                'balance_after' => $after,
                'reference' => $response['reference'] ?? ('wallet-payout-' . $request['id']),
                'notes' => 'Payout de carteira',
            ]);
        }

        return $status === 'completed';
    }

    public function syncWalletPayoutStatus(): void
    {
        foreach ($this->wallets->pendingPayoutRequests() as $request) {
            if (!empty($request['debito_reference'])) {
                $status = $this->payment->checkTransactionStatus($request['debito_reference']);
                $normalized = ($status['status'] ?? '') === 'success' ? 'completed' : (($status['status'] ?? '') === 'failed' ? 'failed' : 'processing');
                $this->wallets->updatePayoutRequest((int)$request['id'], $normalized, $request['debito_reference'], json_encode($status, JSON_THROW_ON_ERROR));
            }
        }
    }
}
