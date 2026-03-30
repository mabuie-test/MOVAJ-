<div class="row g-3 mb-3">
<?php $label='Saldo disponível'; $value='MZN '.number_format((float)($available_balance ?? 0),2); $icon='fa-wallet'; require __DIR__ . '/../components/kpi_card.php'; ?>
<?php $label='Saldo pendente'; $value='MZN '.number_format((float)($pending_balance ?? 0),2); $icon='fa-hourglass-half'; require __DIR__ . '/../components/kpi_card.php'; ?>
<?php $label='Jobs disponíveis'; $value='--'; $icon='fa-motorcycle'; require __DIR__ . '/../components/kpi_card.php'; ?>
<?php $label='Pontualidade'; $value='--'; $icon='fa-bullseye'; require __DIR__ . '/../components/kpi_card.php'; ?>
</div>
<div class="module-card d-flex flex-wrap gap-2 justify-content-between align-items-center">
  <div>
    <h5 class="mb-1">Operação Rider</h5>
    <p class="text-muted small mb-0">Atualize seu status e acompanhe entregas em andamento.</p>
  </div>
  <div class="d-flex gap-2">
    <a href="/rider/jobs" class="btn btn-primary"><i class="fa-solid fa-list-check me-1"></i>Trabalhos disponíveis</a>
    <a href="/rider/earnings" class="btn btn-outline-secondary"><i class="fa-solid fa-wallet me-1"></i>Carteira</a>
  </div>
</div>
