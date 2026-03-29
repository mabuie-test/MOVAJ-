<div class="card"><div class="card-body">
<h3>Tracking Público</h3>
<p>Status: <strong><?= e($order['delivery_status']) ?></strong></p>
<p>Rota estimada: <?= e((string)$order['route_distance_km']) ?> km / <?= e((string)$order['route_duration_minutes']) ?> min</p>
<p>Origem: <?= e($order['pickup_address']) ?></p>
<p>Destino: <?= e($order['dropoff_address']) ?></p>
<form method="post" action="/track/<?= e($order['public_tracking_token']) ?>/otp">
<input type="hidden" name="_token" value="<?= csrf_token() ?>">
<input class="form-control mb-2" name="otp" placeholder="OTP de entrega">
<button class="btn btn-primary">Confirmar OTP/PIN</button>
</form>
</div></div>
