<?php

declare(strict_types=1);

namespace App\Repositories;

use PDO;

class OrderRepository extends BaseRepository
{
    public function create(array $data): int
    {
        $sql = 'INSERT INTO orders (
            merchant_id, public_tracking_token, pickup_contact_name, pickup_contact_phone,
            pickup_address, pickup_reference, pickup_lat, pickup_lng,
            dropoff_contact_name, dropoff_contact_phone, dropoff_address, dropoff_reference,
            dropoff_lat, dropoff_lng, package_type, package_description, package_size, package_weight,
            estimated_value, notes, city, zone, route_distance_km, route_duration_minutes, route_provider,
            route_polyline, geocoding_confidence, base_price, distance_price, urgency_surcharge, extra_fee,
            platform_fee, rider_payout, price_total, pricing_breakdown, payment_method, payment_status,
            payout_status, delivery_status, otp_code, otp_expires_at
        ) VALUES (
            :merchant_id, :public_tracking_token, :pickup_contact_name, :pickup_contact_phone,
            :pickup_address, :pickup_reference, :pickup_lat, :pickup_lng,
            :dropoff_contact_name, :dropoff_contact_phone, :dropoff_address, :dropoff_reference,
            :dropoff_lat, :dropoff_lng, :package_type, :package_description, :package_size, :package_weight,
            :estimated_value, :notes, :city, :zone, :route_distance_km, :route_duration_minutes, :route_provider,
            :route_polyline, :geocoding_confidence, :base_price, :distance_price, :urgency_surcharge, :extra_fee,
            :platform_fee, :rider_payout, :price_total, :pricing_breakdown, :payment_method, :payment_status,
            :payout_status, :delivery_status, :otp_code, :otp_expires_at
        )';
        $stmt = $this->db->prepare($sql);
        $stmt->execute($data);
        return (int)$this->db->lastInsertId();
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM orders WHERE id=:id');
        $stmt->execute(['id' => $id]);
        return $stmt->fetch() ?: null;
    }

    public function findByTrackingToken(string $token): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM orders WHERE public_tracking_token=:token');
        $stmt->execute(['token' => $token]);
        return $stmt->fetch() ?: null;
    }

    public function listByMerchant(int $merchantId): array
    {
        $stmt = $this->db->prepare('SELECT * FROM orders WHERE merchant_id=:merchant_id ORDER BY id DESC');
        $stmt->execute(['merchant_id' => $merchantId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function updateStatuses(int $id, string $paymentStatus, string $deliveryStatus, string $payoutStatus): void
    {
        $stmt = $this->db->prepare('UPDATE orders SET payment_status=:payment_status, delivery_status=:delivery_status, payout_status=:payout_status WHERE id=:id');
        $stmt->execute(['id' => $id, 'payment_status' => $paymentStatus, 'delivery_status' => $deliveryStatus, 'payout_status' => $payoutStatus]);
    }

    public function assignRider(int $orderId, int $riderId): void
    {
        $stmt = $this->db->prepare("UPDATE orders SET assigned_rider_id=:rider_id, delivery_status='accepted' WHERE id=:order_id");
        $stmt->execute(['order_id' => $orderId, 'rider_id' => $riderId]);
    }

    public function updateDeliveryStatus(int $orderId, string $status): void
    {
        $stmt = $this->db->prepare('UPDATE orders SET delivery_status=:status WHERE id=:order_id');
        $stmt->execute(['order_id' => $orderId, 'status' => $status]);
    }

    public function availableJobs(string $city): array
    {
        $stmt = $this->db->prepare("SELECT * FROM orders WHERE city=:city AND payment_status='paid' AND delivery_status IN ('awaiting_assignment','assigned') ORDER BY created_at ASC");
        $stmt->execute(['city' => $city]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function reassignStale(int $maxMinutes): int
    {
        $sql = "UPDATE orders SET assigned_rider_id=NULL, delivery_status='awaiting_assignment' WHERE delivery_status='assigned' AND TIMESTAMPDIFF(MINUTE, updated_at, NOW()) > :max_minutes";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['max_minutes' => $maxMinutes]);
        return $stmt->rowCount();
    }

    public function addHistory(int $orderId, string $status, string $actorType, int $actorId, ?string $notes = null): void
    {
        $stmt = $this->db->prepare('INSERT INTO order_status_history (order_id, status, actor_type, actor_id, notes) VALUES (:order_id,:status,:actor_type,:actor_id,:notes)');
        $stmt->execute([
            'order_id' => $orderId,
            'status' => $status,
            'actor_type' => $actorType,
            'actor_id' => $actorId,
            'notes' => $notes,
        ]);
    }
}
