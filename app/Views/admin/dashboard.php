<div class="row g-3 mb-3">
<?php $label='Pedidos totais'; $value=$kpis['total_orders'] ?? '--'; $icon='fa-box'; require __DIR__ . '/../components/kpi_card.php'; ?>
<?php $label='Pedidos hoje'; $value=$kpis['orders_today'] ?? '--'; $icon='fa-calendar-day'; require __DIR__ . '/../components/kpi_card.php'; ?>
<?php $label='Receita'; $value='MZN '.number_format((float)($kpis['total_revenue'] ?? 0),2); $icon='fa-coins'; require __DIR__ . '/../components/kpi_card.php'; ?>
<?php $label='Falha payout %'; $value=number_format((float)($kpis['payout_fail_rate'] ?? 0),2).'%'; $icon='fa-triangle-exclamation'; require __DIR__ . '/../components/kpi_card.php'; ?>
</div>

<div class="row g-3">
  <div class="col-xl-8">
    <div class="module-card">
      <div class="module-header"><h5 class="mb-0">Monitor Operacional (Mapa)</h5><span class="small text-muted">Pontos de recolha e entrega ativos</span></div>
      <div id="admin-map"></div>
      <div class="d-flex flex-wrap gap-3 mt-2 small">
        <span><i class="fa-solid fa-circle text-success me-1"></i>Pickup</span>
        <span><i class="fa-solid fa-circle text-danger me-1"></i>Dropoff</span>
      </div>
      <script>initAdminMap(<?= json_encode($activeOrders ?? [], JSON_UNESCAPED_UNICODE) ?>);</script>
    </div>
  </div>
  <div class="col-xl-4">
    <div class="module-card h-100">
      <h6 class="fw-bold mb-3">Alertas Operacionais</h6>
      <ul class="timeline">
        <li><span class="timeline-dot"><i class="fa-solid fa-clock"></i></span><div>Monitorar reservas de dispatch expiradas.</div></li>
        <li><span class="timeline-dot"><i class="fa-solid fa-wallet"></i></span><div>Verificar payouts em processamento.</div></li>
        <li><span class="timeline-dot"><i class="fa-solid fa-bullseye"></i></span><div>Acompanhar SLA de recolha e entrega.</div></li>
      </ul>
    </div>
  </div>
</div>
