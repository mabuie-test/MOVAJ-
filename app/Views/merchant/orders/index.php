<div class="card"><div class="card-body">
<div class="d-flex justify-content-between align-items-center mb-3"><h5 class="mb-0">Pedidos do Merchant</h5><a href="/merchant/orders/create" class="btn btn-primary btn-sm"><i class="fa-solid fa-plus me-1"></i>Novo</a></div>
<?php if (empty($orders ?? [])): ?>
  <?php $title='Nenhum pedido ainda'; $message='Crie um novo pedido para iniciar as entregas.'; $icon='fa-box-open'; require __DIR__ . '/../../components/empty_state.php'; ?>
<?php else: ?>
<table class="table table-hover align-middle">
<thead><tr><th>ID</th><th>Status</th><th>Cidade</th><th>Total</th><th>Ações</th></tr></thead>
<tbody>
<?php foreach (($orders ?? []) as $order): ?>
<tr>
<td>#<?= e((string)$order['id']) ?></td>
<td><?php $status=$order['delivery_status']; require __DIR__ . '/../../components/status_badge.php'; ?></td>
<td><?= e($order['city']) ?></td>
<td>MZN <?= e(number_format((float)$order['price_total'],2)) ?></td>
<td class="d-flex gap-2">
  <a class="btn btn-sm btn-outline-primary" href="/merchant/orders/<?= e((string)$order['id']) ?>"><i class="fa-solid fa-eye"></i></a>
  <?php if (($order['delivery_status'] ?? '') === 'pending_payment'): ?>
    <button class="btn btn-sm btn-success" type="button" onclick="quickPayOrder(<?= (int)$order['id'] ?>, '<?= csrf_token() ?>')"><i class="fa-solid fa-bolt me-1"></i>Pagar automático</button>
  <?php endif; ?>
</td>
</tr>
<?php endforeach; ?>
</tbody></table>
<?php endif; ?>
</div></div>
