<div class="card"><div class="card-body">
<h3>Job #<?= e((string)$order['id']) ?></h3>
<p>Status atual: <?= e($order['delivery_status']) ?></p>
<div id="rider-job-map"></div>
<form class="mt-3" method="post" action="/rider/jobs/<?= e((string)$order['id']) ?>/proof" enctype="multipart/form-data">
<input type="hidden" name="_token" value="<?= csrf_token() ?>">
<input class="form-control mb-2" name="recipient_name" placeholder="Nome de quem recebeu">
<input class="form-control mb-2" type="file" name="delivery_photo" accept="image/*">
<textarea class="form-control mb-2" name="recipient_signature" placeholder="Cole assinatura base64 (canvas app mobile)"></textarea>
<textarea class="form-control mb-2" name="notes" placeholder="Observações"></textarea>
<button class="btn btn-success">Enviar prova de entrega</button>
</form>
<script>
  initRiderJobMap({
    pickup: {lat: <?= (float)$order['pickup_lat'] ?>, lng: <?= (float)$order['pickup_lng'] ?>},
    dropoff: {lat: <?= (float)$order['dropoff_lat'] ?>, lng: <?= (float)$order['dropoff_lng'] ?>},
    route: {polyline: <?= json_encode($order['route_polyline']) ?>}
  });
</script>
</div></div>
