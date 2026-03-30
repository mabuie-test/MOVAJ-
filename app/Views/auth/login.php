<div class="row justify-content-center"><div class="col-lg-5 col-md-7">
<div class="card shadow-sm"><div class="card-body p-4">
<h4 class="mb-1 fw-bold">Bem-vindo à MovaJá</h4>
<p class="text-muted mb-3">Entre na sua conta para continuar a operação.</p>
<?php if (!empty($notice)): ?><div class="alert alert-success"><?= e((string)$notice) ?></div><?php endif; ?>
<?php if (!empty($error)): ?><div class="alert alert-danger"><?= e($error) ?></div><?php endif; ?>
<form method="post" action="/login" class="vstack gap-2">
<input type="hidden" name="_token" value="<?= csrf_token() ?>">
<div><label class="form-label">Perfil</label><select name="role" class="form-select"><option value="merchant">Lojista</option><option value="rider">Motoboy</option></select></div>
<div><label class="form-label"><i class="fa-regular fa-envelope me-1"></i>Email</label><input class="form-control" type="email" name="email" required></div>
<div><label class="form-label"><i class="fa-solid fa-lock me-1"></i>Password</label><input class="form-control" type="password" name="password" required></div>
<button class="btn btn-primary w-100 mt-2">Entrar</button>
</form>
<div class="text-center mt-3 small"><a href="/forgot-password">Esqueceu a senha?</a></div>
</div></div></div></div>
