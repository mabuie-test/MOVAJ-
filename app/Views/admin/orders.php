<div class="module-card">
  <div class="module-header">
    <div><h5 class="mb-1"><i class="fa-solid fa-box me-2"></i>Pedidos (Admin)</h5><p class="text-muted small mb-0">Controle operacional, dispatch e intervenções.</p></div>
    <div class="filter-bar">
      <select class="form-select"><option>Status</option></select>
      <input class="form-control" type="date">
      <button class="btn btn-outline-secondary"><i class="fa-solid fa-filter me-1"></i>Filtrar</button>
    </div>
  </div>
  <table class="table table-hover align-middle">
    <thead><tr><th>ID</th><th>Status</th><th>Cidade</th><th>Distância</th><th>Ações</th></tr></thead>
    <tbody>
      <tr>
        <td colspan="5"><?php $title='Sem dados carregados'; $message='Conecte a listagem de pedidos para operar intervenções.'; $icon='fa-boxes-stacked'; require __DIR__ . '/../components/empty_state.php'; ?></td>
      </tr>
    </tbody>
  </table>
</div>
