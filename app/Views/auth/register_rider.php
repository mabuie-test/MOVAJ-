<div class="card"><div class="card-body p-4"><h4 class="fw-bold mb-3"><i class="fa-solid fa-motorcycle me-2"></i>Cadastro de Motoboy</h4>
<form method="post" action="/register/rider">
<input type="hidden" name="_token" value="<?= csrf_token() ?>">
<div class="row g-3">
<div class="col-md-6"><label class="form-label">Nome</label><input class="form-control" name="name" required></div>
<div class="col-md-6"><label class="form-label">Email</label><input class="form-control" type="email" name="email" required></div>
<div class="col-md-6"><label class="form-label">Telefone</label><input class="form-control" name="phone" required></div>
<div class="col-md-6"><label class="form-label">Cidade</label><input class="form-control" name="city" required></div>
<div class="col-md-6"><label class="form-label">Zona</label><input class="form-control" name="zone"></div>
<div class="col-md-6"><label class="form-label">Carteira</label><select class="form-select" name="wallet_provider"><option value="mpesa">M-Pesa</option><option value="emola">eMola</option></select></div>
<div class="col-md-6"><label class="form-label">Mota</label><input class="form-control" name="bike_number"></div>
<div class="col-md-6"><label class="form-label">Senha</label><input class="form-control" type="password" name="password" required></div>
</div><button class="btn btn-primary mt-3">Criar conta</button></form>
</div></div>
