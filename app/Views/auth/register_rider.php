<div class="card"><div class="card-body p-4"><h4 class="fw-bold mb-3"><i class="fa-solid fa-motorcycle me-2"></i>Cadastro de Motoboy</h4>
<?php if (!empty($error)): ?><div class="alert alert-danger"><?= e((string)$error) ?></div><?php endif; ?>
<form method="post" action="/register/rider" enctype="multipart/form-data">
<input type="hidden" name="_token" value="<?= csrf_token() ?>">
<div class="row g-3">
<div class="col-md-6"><label class="form-label">Nome</label><input class="form-control" name="name" required></div>
<div class="col-md-6"><label class="form-label">Email</label><input class="form-control" type="email" name="email" required></div>
<div class="col-md-6"><label class="form-label">Telefone</label><input class="form-control" name="phone" required></div>
<div class="col-md-6"><label class="form-label">Contacto alternativo (sem verificação)</label><input class="form-control" name="emergency_contact_phone"></div>
<div class="col-md-6"><label class="form-label">Cidade</label><input class="form-control" name="city" required></div>
<div class="col-md-6"><label class="form-label">Zona</label><input class="form-control" name="zone"></div>
<div class="col-md-6"><label class="form-label">Carteira</label><select class="form-select" name="wallet_provider"><option value="mpesa">M-Pesa</option><option value="emola">eMola</option></select></div>
<div class="col-md-6"><label class="form-label">Mota</label><input class="form-control" name="bike_number"></div>
<div class="col-md-6"><label class="form-label">Nº do BI</label><input class="form-control" name="id_number" required></div>
<div class="col-md-3"><label class="form-label">Emissão BI</label><input class="form-control" type="date" name="id_issue_date" required></div>
<div class="col-md-3"><label class="form-label">Expiração BI</label><input class="form-control" type="date" name="id_expiry_date" required></div>
<div class="col-md-6"><label class="form-label">NUIT</label><input class="form-control" name="nuit" required></div>
<div class="col-md-6"><label class="form-label">Endereço</label><input class="form-control" name="address_line" required></div>
<div class="col-md-6"><label class="form-label">Foto BI (frente)</label><input class="form-control" type="file" name="bi_front" accept="image/*" required></div>
<div class="col-md-6"><label class="form-label">Foto BI (verso)</label><input class="form-control" type="file" name="bi_back" accept="image/*" required></div>
<div class="col-md-6"><label class="form-label">Foto do rosto</label><input class="form-control" type="file" name="selfie_photo" accept="image/*" required></div>
<div class="col-md-6"><label class="form-label">Senha</label><input class="form-control" type="password" name="password" required></div>
</div><button class="btn btn-primary mt-3">Criar conta</button></form>
</div></div>
