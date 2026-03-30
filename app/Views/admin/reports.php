<div class="module-card">
  <div class="module-header">
    <div><h5 class="mb-1"><i class="fa-solid fa-file-lines me-2"></i>Relatórios Admin</h5><p class="text-muted small mb-0">SLA, pagamentos, payouts e performance por zona.</p></div>
    <button class="btn btn-outline-primary"><i class="fa-solid fa-file-export me-1"></i>Exportar</button>
  </div>
  <div class="row g-3 mb-3">
    <?php $label='SLA médio'; $value='--'; $icon='fa-gauge-high'; require __DIR__ . '/../components/kpi_card.php'; ?>
    <?php $label='Atrasos críticos'; $value='--'; $icon='fa-triangle-exclamation'; require __DIR__ . '/../components/kpi_card.php'; ?>
    <?php $label='Sucesso payout'; $value='--'; $icon='fa-circle-check'; require __DIR__ . '/../components/kpi_card.php'; ?>
    <?php $label='Volume carteira'; $value='--'; $icon='fa-wallet'; require __DIR__ . '/../components/kpi_card.php'; ?>
  </div>
  <pre class="bg-light border rounded p-3 mb-0"><?= e(json_encode($report ?? [], JSON_PRETTY_PRINT)) ?></pre>
</div>
