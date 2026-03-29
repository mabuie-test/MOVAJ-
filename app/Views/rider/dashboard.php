<div class="card"><div class="card-body">
<h3>Dashboard Rider</h3>
<div class="row">
<div class="col-md-6"><div class="alert alert-info">Saldo disponível: <?= e(number_format((float)($available_balance ?? 0),2)) ?></div></div>
<div class="col-md-6"><div class="alert alert-warning">Saldo pendente: <?= e(number_format((float)($pending_balance ?? 0),2)) ?></div></div>
</div>
<a href="/rider/jobs" class="btn btn-primary">Ver Jobs</a>
</div></div>
