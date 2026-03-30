<div class="module-card">
  <div class="module-header">
    <div><h5 class="mb-1"><i class="fa-solid fa-wallet me-2"></i>Payouts e Wallet</h5><p class="text-muted small mb-0">Solicitações, processamento e falhas de payout.</p></div>
  </div>
  <table class="table table-hover align-middle">
    <thead><tr><th>Rider</th><th>Provider</th><th>Valor</th><th>Status</th><th class="text-end">Ações</th></tr></thead>
    <tbody>
      <tr>
        <td colspan="5"><?php $title='Nenhum payout pendente'; $message='As solicitações aparecerão nesta tabela.'; $icon='fa-wallet'; require __DIR__ . '/../components/empty_state.php'; ?></td>
      </tr>
    </tbody>
  </table>
</div>
