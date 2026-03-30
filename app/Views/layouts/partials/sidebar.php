<?php
$path = strtok($_SERVER['REQUEST_URI'] ?? '/', '?');
$menu = [];
if (str_starts_with($path, '/admin')) {
    $menu = [
        ['/admin', 'Dashboard', 'fa-chart-line'],
        ['/admin/orders', 'Pedidos', 'fa-box'],
        ['/admin/payments', 'Pagamentos', 'fa-money-bill-wave'],
        ['/admin/payouts', 'Payouts', 'fa-wallet'],
        ['/admin/reports', 'Relatórios', 'fa-file-lines'],
    ];
} elseif (str_starts_with($path, '/rider')) {
    $menu = [
        ['/rider/dashboard', 'Dashboard', 'fa-gauge-high'],
        ['/rider/jobs', 'Jobs', 'fa-motorcycle'],
        ['/rider/earnings', 'Carteira', 'fa-wallet'],
    ];
} elseif (str_starts_with($path, '/merchant')) {
    $menu = [
        ['/merchant/dashboard', 'Dashboard', 'fa-gauge-high'],
        ['/merchant/orders', 'Pedidos', 'fa-box-open'],
        ['/merchant/orders/create', 'Novo Pedido', 'fa-route'],
        ['/merchant/reports', 'Relatórios', 'fa-chart-pie'],
    ];
}
?>
<?php if (!empty($menu)): ?>
<aside class="app-sidebar">
  <div class="sidebar-title">MovaJá</div>
  <?php foreach ($menu as [$href, $label, $icon]): ?>
    <a class="sidebar-link <?= str_starts_with($path, $href) ? 'active' : '' ?>" href="<?= e($href) ?>">
      <i class="fa-solid <?= e($icon) ?>"></i><span><?= e($label) ?></span>
    </a>
  <?php endforeach; ?>
</aside>
<?php endif; ?>
