<div class="card"><div class="card-body">
<div class="d-flex justify-content-between align-items-center mb-2">
  <h5 class="mb-0">Detalhe do Pedido #<?= e((string)$order['id']) ?></h5>
  <?php $status = $order['delivery_status']; require __DIR__ . '/../../components/status_badge.php'; ?>
</div>
<div class="map-meta mb-3">
  <div class="meta-item"><div class="meta-label">Distância</div><div class="meta-value"><?= e((string)$order['route_distance_km']) ?> km</div></div>
  <div class="meta-item"><div class="meta-label">ETA</div><div class="meta-value"><?= e((string)$order['route_duration_minutes']) ?> min</div></div>
  <div class="meta-item"><div class="meta-label">Preço total</div><div class="meta-value">MZN <?= e(number_format((float)$order['price_total'],2)) ?></div></div>
  <div class="meta-item"><div class="meta-label">Rider payout</div><div class="meta-value">MZN <?= e(number_format((float)$order['rider_payout'],2)) ?></div></div>
</div>
<div id="tracking-map"></div>
<?php if (!empty($proof)): ?>
<hr>
<h6 class="fw-bold"><i class="fa-solid fa-camera me-1"></i>Prova de Entrega</h6>
<div class="row g-3">
<div class="col-md-6"><?php if (!empty($proof['delivery_photo_path'])): ?><img src="/../<?= e($proof['delivery_photo_path']) ?>" class="img-fluid rounded border" alt="proof-photo"><?php endif; ?></div>
<div class="col-md-6">
  <p class="mb-1"><strong>Recebedor:</strong> <?= e((string)($proof['recipient_name'] ?? 'N/A')) ?></p>
  <p class="mb-1"><strong>Entregue em:</strong> <?= e((string)($proof['delivered_at'] ?? 'N/A')) ?></p>
  <p class="mb-0"><strong>OTP validado:</strong> <?= !empty($proof['otp_validated']) ? 'Sim' : 'Não' ?></p>
</div>
</div>
<?php endif; ?>
<script>
  initTrackingMap(<?= json_encode($mapPayload ?? [], JSON_UNESCAPED_UNICODE) ?>, '<?= e($order['public_tracking_token']) ?>', 15);
</script>
</div></div>
