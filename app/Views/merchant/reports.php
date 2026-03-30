<div class="module-card">
  <div class="module-header">
    <div>
      <h5 class="mb-1"><i class="fa-solid fa-chart-pie me-2"></i>Relatórios do Merchant</h5>
      <p class="text-muted mb-0 small">Custos por rota, SLA e histórico operacional.</p>
    </div>
    <div class="filter-bar">
      <input class="form-control" type="date">
      <input class="form-control" type="date">
      <button class="btn btn-outline-primary"><i class="fa-solid fa-file-export me-1"></i>Exportar</button>
    </div>
  </div>
  <div class="row g-3 mb-3">
    <?php $label='Tempo médio'; $value='--'; $icon='fa-clock'; require __DIR__ . '/../components/kpi_card.php'; ?>
    <?php $label='SLA cumprido'; $value='--'; $icon='fa-bullseye'; require __DIR__ . '/../components/kpi_card.php'; ?>
    <?php $label='Custo médio/rota'; $value='--'; $icon='fa-money-bill-wave'; require __DIR__ . '/../components/kpi_card.php'; ?>
    <?php $label='Entregas atrasadas'; $value='--'; $icon='fa-triangle-exclamation'; require __DIR__ . '/../components/kpi_card.php'; ?>
  </div>
  <?php $title='Sem relatório consolidado neste período'; $message='Selecione um intervalo para gerar indicadores.'; $icon='fa-chart-column'; require __DIR__ . '/../components/empty_state.php'; ?>
</div>
