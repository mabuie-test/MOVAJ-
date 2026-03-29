<div class="card"><div class="card-body"><h3>Cadastro Merchant</h3>
<form method="post" action="/register/merchant">
<input type="hidden" name="_token" value="<?= csrf_token() ?>">
<div class="row g-2">
<div class="col-md-6"><input class="form-control" name="business_name" placeholder="Nome do negócio" required></div>
<div class="col-md-6"><input class="form-control" name="owner_name" placeholder="Responsável" required></div>
<div class="col-md-6"><input class="form-control" type="email" name="email" placeholder="Email" required></div>
<div class="col-md-6"><input class="form-control" name="phone" placeholder="Telefone" required></div>
<div class="col-md-6"><input class="form-control" name="city" placeholder="Cidade" required></div>
<div class="col-md-6"><input class="form-control" type="password" name="password" placeholder="Senha" required></div>
</div><button class="btn btn-primary mt-3">Criar conta merchant</button></form>
</div></div>
