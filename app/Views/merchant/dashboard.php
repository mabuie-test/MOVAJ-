<div class="row g-3 mb-3">
<?php $label='Pedidos ativos'; $value='--'; $icon='fa-box'; require __DIR__ . '/../components/kpi_card.php'; ?>
<?php $label='Em trânsito'; $value='--'; $icon='fa-motorcycle'; require __DIR__ . '/../components/kpi_card.php'; ?>
<?php $label='Entregues'; $value='--'; $icon='fa-circle-check'; require __DIR__ . '/../components/kpi_card.php'; ?>
<?php $label='Total gasto'; $value='--'; $icon='fa-money-bill-wave'; require __DIR__ . '/../components/kpi_card.php'; ?>
</div>
<div class="card"><div class="card-body d-flex flex-wrap gap-2 align-items-center justify-content-between">
<div>
  <h5 class="mb-1">Painel do Lojista</h5>
  <p class="text-muted mb-0">Acompanhe pedidos, tracking e desempenho operacional.</p>
</div>
<div class="d-flex gap-2">
  <a class="btn btn-primary" href="/merchant/orders/create"><i class="fa-solid fa-plus me-1"></i>Novo pedido</a>
  <a class="btn btn-outline-secondary" href="/merchant/orders"><i class="fa-solid fa-route me-1"></i>Ver pedidos</a>
</div>
</div></div>
