<div class="row justify-content-center"><div class="col-md-6">
<div class="card shadow-sm"><div class="card-body">
<h3 class="mb-3">Login</h3>
<?php if (!empty($error)): ?><div class="alert alert-danger"><?= e($error) ?></div><?php endif; ?>
<form method="post" action="/login">
<input type="hidden" name="_token" value="<?= csrf_token() ?>">
<div class="mb-2"><label>Perfil</label><select name="role" class="form-select"><option value="merchant">Merchant</option><option value="rider">Rider</option><option value="admin">Admin</option></select></div>
<div class="mb-2"><label>Email</label><input class="form-control" type="email" name="email" required></div>
<div class="mb-2"><label>Password</label><input class="form-control" type="password" name="password" required></div>
<button class="btn btn-dark w-100">Entrar</button>
</form>
</div></div></div></div>
