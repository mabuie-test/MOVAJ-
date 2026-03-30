<div class="row g-3 mb-3">
<?php $label='Pedidos totais'; $value=$kpis['total_orders'] ?? '--'; $icon='fa-box'; require __DIR__ . '/../components/kpi_card.php'; ?>
<?php $label='Pedidos hoje'; $value=$kpis['orders_today'] ?? '--'; $icon='fa-calendar-day'; require __DIR__ . '/../components/kpi_card.php'; ?>
<?php $label='Receita'; $value='MZN '.number_format((float)($kpis['total_revenue'] ?? 0),2); $icon='fa-coins'; require __DIR__ . '/../components/kpi_card.php'; ?>
<?php $label='Falha payout %'; $value=number_format((float)($kpis['payout_fail_rate'] ?? 0),2).'%'; $icon='fa-triangle-exclamation'; require __DIR__ . '/../components/kpi_card.php'; ?>
</div>
<div class="card"><div class="card-body">
<div class="d-flex justify-content-between align-items-center mb-2"><h5 class="mb-0">Monitor Operacional (Mapa)</h5><span class="text-muted small">Pedidos ativos</span></div>
<div id="admin-map"></div>
<script>initAdminMap(<?= json_encode($activeOrders ?? [], JSON_UNESCAPED_UNICODE) ?>);</script>
</div></div>
