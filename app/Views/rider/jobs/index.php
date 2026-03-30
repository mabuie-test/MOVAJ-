<div class="card"><div class="card-body"><h5 class="mb-3">Fila Operacional de Jobs</h5>
<?php if (empty($jobs ?? [])): ?>
  <?php $title='Sem jobs disponíveis'; $message='Volte em instantes para novas reservas/atribuições.'; $icon='fa-motorcycle'; require __DIR__ . '/../../components/empty_state.php'; ?>
<?php else: ?>
<table class="table align-middle">
<thead><tr><th>Pedido</th><th>Origem</th><th>Destino</th><th>Km</th><th>Payout</th><th></th></tr></thead>
<tbody>
<?php foreach (($jobs ?? []) as $job): ?>
<tr>
<td>#<?= e((string)$job['id']) ?></td>
<td><?= e($job['pickup_address']) ?></td>
<td><?= e($job['dropoff_address']) ?></td>
<td><?= e((string)$job['route_distance_km']) ?></td>
<td>MZN <?= e(number_format((float)$job['rider_payout'],2)) ?></td>
<td>
<form method="post" action="/rider/jobs/<?= e((string)$job['id']) ?>/accept">
<input type="hidden" name="_token" value="<?= csrf_token() ?>">
<button class="btn btn-primary btn-sm"><i class="fa-solid fa-handshake me-1"></i>Aceitar</button>
</form>
</td>
</tr>
<?php endforeach; ?>
</tbody></table>
<?php endif; ?>
</div></div>
