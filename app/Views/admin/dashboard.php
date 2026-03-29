<div class="card"><div class="card-body"><h3>Admin dashboard</h3>
<pre><?= e(json_encode($kpis ?? [], JSON_PRETTY_PRINT)) ?></pre>
<div id="admin-map"></div>
<script>
  initAdminMap(<?= json_encode($activeOrders ?? [], JSON_UNESCAPED_UNICODE) ?>);
</script>
</div></div>
