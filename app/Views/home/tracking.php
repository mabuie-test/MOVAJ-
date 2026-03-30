<div class="card"><div class="card-body">
<h4 class="fw-bold"><i class="fa-solid fa-route me-2"></i>Tracking Público</h4>
<div class="d-flex gap-2 flex-wrap mb-2">
  <?php $status = $order['delivery_status']; require __DIR__ . '/../components/status_badge.php'; ?>
</div>
<div class="map-meta mb-3">
  <div class="meta-item"><div class="meta-label">Distância</div><div class="meta-value"><?= e((string)$order['route_distance_km']) ?> km</div></div>
  <div class="meta-item"><div class="meta-label">ETA</div><div class="meta-value"><?= e((string)$order['route_duration_minutes']) ?> min</div></div>
  <div class="meta-item"><div class="meta-label">Origem</div><div class="meta-value small"><?= e($order['pickup_address']) ?></div></div>
  <div class="meta-item"><div class="meta-label">Destino</div><div class="meta-value small"><?= e($order['dropoff_address']) ?></div></div>
</div>
<div id="tracking-map" class="mb-3"></div>
<form method="post" action="/track/<?= e($order['public_tracking_token']) ?>/otp" class="row g-2">
<input type="hidden" name="_token" value="<?= csrf_token() ?>">
<div class="col-md-8"><input class="form-control" name="otp" placeholder="Insira o OTP/PIN"></div>
<div class="col-md-4 d-grid"><button class="btn btn-success"><i class="fa-solid fa-circle-check me-1"></i>Confirmar entrega</button></div>
</form>
<?php if (!empty($proof)): ?><div class="alert alert-success mt-3"><i class="fa-solid fa-shield-check me-2"></i>Entrega confirmada em <?= e((string)$proof['delivered_at']) ?></div><?php endif; ?>
<script>
  initTrackingMap(<?= json_encode($mapPayload ?? [], JSON_UNESCAPED_UNICODE) ?>, '<?= e($order['public_tracking_token']) ?>', <?= (int)($_ENV['TRACKING_POLL_SECONDS'] ?? 15) ?>);
</script>
</div></div>
