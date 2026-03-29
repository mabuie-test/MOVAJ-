<div class="card"><div class="card-body">
<h3>Detalhe do Pedido #<?= e((string)$order['id']) ?></h3>
<p>Status: <?= e($order['delivery_status']) ?> | Distância: <?= e((string)$order['route_distance_km']) ?> km</p>
<div id="tracking-map"></div>
<?php if (!empty($proof)): ?>
<hr>
<h5>Prova de Entrega</h5>
<p>Recebedor: <?= e((string)($proof['recipient_name'] ?? 'N/A')) ?></p>
<?php if (!empty($proof['delivery_photo_path'])): ?><img src="/../<?= e($proof['delivery_photo_path']) ?>" class="img-fluid mb-2" alt="proof-photo"><?php endif; ?>
<?php endif; ?>
<script>
  initTrackingMap(<?= json_encode($mapPayload ?? [], JSON_UNESCAPED_UNICODE) ?>, '<?= e($order['public_tracking_token']) ?>', 15);
</script>
</div></div>
