<div class="card"><div class="card-body"><h3>Pedidos do Merchant</h3>
<table class="table table-striped">
<thead><tr><th>ID</th><th>Status</th><th>Cidade</th><th>Total</th><th>Ações</th></tr></thead>
<tbody>
<?php foreach (($orders ?? []) as $order): ?>
<tr>
<td>#<?= e((string)$order['id']) ?></td>
<td><?= e($order['delivery_status']) ?> / <?= e($order['payment_status']) ?></td>
<td><?= e($order['city']) ?></td>
<td><?= e((string)$order['price_total']) ?></td>
<td><a class="btn btn-sm btn-outline-primary" href="/merchant/orders/<?= e((string)$order['id']) ?>">Ver</a></td>
</tr>
<?php endforeach; ?>
</tbody></table>
</div></div>
