<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\ProofRepository;

class ProofOfDeliveryService
{
    public function __construct(private readonly ProofRepository $proofs = new ProofRepository()) {}

    public function storeDeliveryPhoto(array $file): ?string
    {
        if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            return null;
        }
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, ['jpg', 'jpeg', 'png', 'webp'], true)) {
            return null;
        }
        $target = 'storage/uploads/proof_of_delivery/' . uniqid('photo_', true) . '.' . $ext;
        @mkdir(dirname($target), 0777, true);
        move_uploaded_file($file['tmp_name'], $target);
        return $target;
    }

    public function storeRecipientSignature(string $base64): ?string
    {
        if (!str_starts_with($base64, 'data:image/')) {
            return null;
        }
        [$meta, $data] = explode(',', $base64, 2);
        $target = 'storage/uploads/proof_of_delivery/' . uniqid('sign_', true) . '.png';
        @mkdir(dirname($target), 0777, true);
        file_put_contents($target, base64_decode($data));
        return $target;
    }

    public function attachProofToOrder(int $orderId, int $riderId, ?string $recipient, ?string $photoPath, ?string $signPath, bool $otpValidated, ?string $notes): void
    {
        $this->proofs->upsert([
            'order_id' => $orderId,
            'rider_id' => $riderId,
            'recipient_name' => $recipient,
            'delivery_photo_path' => $photoPath,
            'recipient_signature_path' => $signPath,
            'otp_validated' => $otpValidated ? 1 : 0,
            'delivered_at' => date('Y-m-d H:i:s'),
            'notes' => $notes,
        ]);
    }

    public function generateProofSummary(int $orderId): array
    {
        return $this->proofs->findByOrder($orderId) ?? [];
    }
}
