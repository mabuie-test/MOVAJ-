<div class="card"><div class="card-body"><h3>Ganhos e carteira</h3>
<p>Disponível: <?= e(number_format((float)($available_balance ?? 0),2)) ?></p>
<p>Pendente: <?= e(number_format((float)($pending_balance ?? 0),2)) ?></p>
</div></div>
