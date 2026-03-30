<div class="module-card">
  <div class="module-header">
    <div>
      <h5 class="mb-1"><i class="fa-solid fa-wallet me-2"></i>Carteira e Ganhos</h5>
      <p class="text-muted mb-0 small">Resumo financeiro, movimentações e payouts.</p>
    </div>
    <button class="btn btn-primary"><i class="fa-solid fa-money-bill-transfer me-1"></i>Solicitar payout</button>
  </div>
  <div class="row g-3 mb-3">
    <?php $label='Disponível'; $value='MZN '.number_format((float)($available_balance ?? 0),2); $icon='fa-coins'; require __DIR__ . '/../components/kpi_card.php'; ?>
    <?php $label='Pendente'; $value='MZN '.number_format((float)($pending_balance ?? 0),2); $icon='fa-hourglass-half'; require __DIR__ . '/../components/kpi_card.php'; ?>
    <?php $label='Ganhos hoje'; $value='--'; $icon='fa-calendar-day'; require __DIR__ . '/../components/kpi_card.php'; ?>
    <?php $label='Payouts'; $value='--'; $icon='fa-money-bill-wave'; require __DIR__ . '/../components/kpi_card.php'; ?>
  </div>
  <table class="table table-hover align-middle">
    <thead><tr><th>Data</th><th>Tipo</th><th>Referência</th><th>Valor</th><th>Status</th></tr></thead>
    <tbody>
      <tr><td colspan="5"><?php $title='Sem movimentações'; $message='As transações da carteira aparecerão aqui.'; $icon='fa-receipt'; require __DIR__ . '/../components/empty_state.php'; ?></td></tr>
    </tbody>
  </table>
</div>
