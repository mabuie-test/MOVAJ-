<?php

declare(strict_types=1);

namespace App\Repositories;

class WalletRepository extends BaseRepository
{
    public function createWallet(int $riderId): void
    {
        $stmt = $this->db->prepare('INSERT IGNORE INTO rider_wallets (rider_id) VALUES (:rider_id)');
        $stmt->execute(['rider_id' => $riderId]);
    }

    public function walletByRider(int $riderId): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM rider_wallets WHERE rider_id=:rider_id LIMIT 1');
        $stmt->execute(['rider_id' => $riderId]);
        return $stmt->fetch() ?: null;
    }

    public function updateBalances(int $walletId, float $available, float $pending, float $credited, float $paidOut): void
    {
        $stmt = $this->db->prepare('UPDATE rider_wallets SET available_balance=:a,pending_balance=:p,total_credited=:c,total_paid_out=:o WHERE id=:id');
        $stmt->execute(['id'=>$walletId,'a'=>$available,'p'=>$pending,'c'=>$credited,'o'=>$paidOut]);
    }

    public function addTransaction(array $data): void
    {
        $stmt = $this->db->prepare('INSERT INTO rider_wallet_transactions (rider_wallet_id,order_id,type,amount,balance_before,balance_after,reference,notes) VALUES (:rider_wallet_id,:order_id,:type,:amount,:balance_before,:balance_after,:reference,:notes)');
        $stmt->execute($data);
    }

    public function createPayoutRequest(array $data): int
    {
        $stmt = $this->db->prepare('INSERT INTO rider_wallet_payout_requests (rider_wallet_id,provider,phone,amount,status,debito_reference,raw_response) VALUES (:rider_wallet_id,:provider,:phone,:amount,:status,:debito_reference,:raw_response)');
        $stmt->execute($data);
        return (int)$this->db->lastInsertId();
    }

    public function pendingPayoutRequests(): array
    {
        return $this->db->query("SELECT * FROM rider_wallet_payout_requests WHERE status IN ('pending','processing') ORDER BY id ASC")->fetchAll();
    }

    public function updatePayoutRequest(int $id, string $status, ?string $debitoReference, ?string $rawResponse): void
    {
        $stmt = $this->db->prepare('UPDATE rider_wallet_payout_requests SET status=:status, debito_reference=:ref, raw_response=:raw WHERE id=:id');
        $stmt->execute(['id'=>$id,'status'=>$status,'ref'=>$debitoReference,'raw'=>$rawResponse]);
    }
}
