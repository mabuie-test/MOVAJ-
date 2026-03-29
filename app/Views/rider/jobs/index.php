<div class="card"><div class="card-body"><h3>Fila Operacional de Jobs</h3>
<table class="table table-sm">
<thead><tr><th>Pedido</th><th>Origem</th><th>Destino</th><th>Km</th><th>Payout</th><th></th></tr></thead>
<tbody>
<?php foreach (($jobs ?? []) as $job): ?>
<tr>
<td>#<?= e((string)$job['id']) ?></td>
<td><?= e($job['pickup_address']) ?></td>
<td><?= e($job['dropoff_address']) ?></td>
<td><?= e((string)$job['route_distance_km']) ?></td>
<td><?= e((string)$job['rider_payout']) ?></td>
<td>
<form method="post" action="/rider/jobs/<?= e((string)$job['id']) ?>/accept">
<input type="hidden" name="_token" value="<?= csrf_token() ?>">
<button class="btn btn-primary btn-sm">Aceitar</button>
</form>
</td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
</div></div>
