<div class="card"><div class="card-body">
<h3>Tracking Público</h3>
<p>Status: <strong><?= e($order['delivery_status']) ?></strong></p>
<p>Rota estimada: <?= e((string)$order['route_distance_km']) ?> km / <?= e((string)$order['route_duration_minutes']) ?> min</p>
<div id="tracking-map" class="mb-3"></div>
<form method="post" action="/track/<?= e($order['public_tracking_token']) ?>/otp">
<input type="hidden" name="_token" value="<?= csrf_token() ?>">
<input class="form-control mb-2" name="otp" placeholder="OTP de entrega">
<button class="btn btn-primary">Confirmar OTP/PIN</button>
</form>
<?php if (!empty($proof)): ?><div class="alert alert-success mt-3">Entrega confirmada em <?= e((string)$proof['delivered_at']) ?></div><?php endif; ?>
<script>
  initTrackingMap(<?= json_encode($mapPayload ?? [], JSON_UNESCAPED_UNICODE) ?>, '<?= e($order['public_tracking_token']) ?>', <?= (int)($_ENV['TRACKING_POLL_SECONDS'] ?? 15) ?>);
</script>
</div></div>
