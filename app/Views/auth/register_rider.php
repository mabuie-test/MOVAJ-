<div class="card"><div class="card-body"><h3>Cadastro Rider</h3>
<form method="post" action="/register/rider">
<input type="hidden" name="_token" value="<?= csrf_token() ?>">
<div class="row g-2">
<div class="col-md-6"><input class="form-control" name="name" placeholder="Nome" required></div>
<div class="col-md-6"><input class="form-control" type="email" name="email" placeholder="Email" required></div>
<div class="col-md-6"><input class="form-control" name="phone" placeholder="Telefone" required></div>
<div class="col-md-6"><input class="form-control" name="city" placeholder="Cidade" required></div>
<div class="col-md-6"><input class="form-control" name="zone" placeholder="Zona"></div>
<div class="col-md-6"><select class="form-select" name="wallet_provider"><option value="mpesa">M-Pesa</option><option value="emola">eMola</option></select></div>
<div class="col-md-6"><input class="form-control" name="bike_number" placeholder="Mota (opcional)"></div>
<div class="col-md-6"><input class="form-control" type="password" name="password" placeholder="Senha" required></div>
</div><button class="btn btn-primary mt-3">Criar conta rider</button></form>
</div></div>
