<div class="card"><div class="card-body">
<div class="d-flex justify-content-between align-items-center mb-2">
  <h5 class="mb-0">Job #<?= e((string)$order['id']) ?></h5>
  <?php $status = $order['delivery_status']; require __DIR__ . '/../../components/status_badge.php'; ?>
</div>
<div class="map-meta mb-3">
  <div class="meta-item"><div class="meta-label">Origem</div><div class="meta-value small"><?= e($order['pickup_address']) ?></div></div>
  <div class="meta-item"><div class="meta-label">Destino</div><div class="meta-value small"><?= e($order['dropoff_address']) ?></div></div>
  <div class="meta-item"><div class="meta-label">Distância</div><div class="meta-value"><?= e((string)$order['route_distance_km']) ?> km</div></div>
  <div class="meta-item"><div class="meta-label">Payout</div><div class="meta-value">MZN <?= e(number_format((float)$order['rider_payout'],2)) ?></div></div>
</div>
<div id="rider-job-map"></div>
<hr>
<h6><i class="fa-solid fa-camera me-1"></i>Prova de Entrega</h6>
<form class="mt-2" method="post" action="/rider/jobs/<?= e((string)$order['id']) ?>/proof" enctype="multipart/form-data">
<input type="hidden" name="_token" value="<?= csrf_token() ?>">
<div class="row g-2">
<div class="col-md-6"><input class="form-control" name="recipient_name" placeholder="Nome de quem recebeu"></div>
<div class="col-md-6"><input class="form-control" type="file" name="delivery_photo" accept="image/*"></div>
<div class="col-12"><textarea class="form-control" name="recipient_signature" rows="2" placeholder="Assinatura base64 (canvas app mobile)"></textarea></div>
<div class="col-12"><textarea class="form-control" name="notes" rows="2" placeholder="Observações"></textarea></div>
</div>
<button class="btn btn-success mt-2"><i class="fa-solid fa-circle-check me-1"></i>Enviar prova</button>
</form>
<script>
  initRiderJobMap({
    pickup: {lat: <?= (float)$order['pickup_lat'] ?>, lng: <?= (float)$order['pickup_lng'] ?>},
    dropoff: {lat: <?= (float)$order['dropoff_lat'] ?>, lng: <?= (float)$order['dropoff_lng'] ?>},
    route: {polyline: <?= json_encode($order['route_polyline']) ?>}
  });
</script>
</div></div>
