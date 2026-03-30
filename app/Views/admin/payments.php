<div class="module-card">
  <div class="module-header">
    <div><h5 class="mb-1"><i class="fa-solid fa-money-bill-wave me-2"></i>Pagamentos C2B</h5><p class="text-muted small mb-0">Conciliação por método, cidade e período.</p></div>
    <div class="filter-bar"><button class="btn btn-outline-primary"><i class="fa-solid fa-file-export me-1"></i>Exportar CSV</button></div>
  </div>
  <div class="row g-3 mb-3">
    <?php $label='Recebido hoje'; $value='--'; $icon='fa-coins'; require __DIR__ . '/../components/kpi_card.php'; ?>
    <?php $label='Taxa de falha'; $value='--'; $icon='fa-triangle-exclamation'; require __DIR__ . '/../components/kpi_card.php'; ?>
    <?php $label='M-Pesa'; $value='--'; $icon='fa-mobile-screen'; require __DIR__ . '/../components/kpi_card.php'; ?>
    <?php $label='eMola'; $value='--'; $icon='fa-mobile-screen-button'; require __DIR__ . '/../components/kpi_card.php'; ?>
  </div>
</div>
