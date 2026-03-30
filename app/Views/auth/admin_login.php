<div class="row justify-content-center"><div class="col-lg-4 col-md-6">
<div class="card shadow-sm"><div class="card-body p-4">
<h4 class="mb-1 fw-bold">Login do Administrador</h4>
<p class="text-muted mb-3">Acesso restrito ao painel administrativo.</p>
<?php if (!empty($error)): ?><div class="alert alert-danger"><?= e((string)$error) ?></div><?php endif; ?>
<form method="post" action="/admin/login" class="vstack gap-2">
<input type="hidden" name="_token" value="<?= csrf_token() ?>">
<div><label class="form-label"><i class="fa-regular fa-envelope me-1"></i>Email admin</label><input class="form-control" type="email" name="email" required></div>
<div><label class="form-label"><i class="fa-solid fa-lock me-1"></i>Password</label><input class="form-control" type="password" name="password" required></div>
<button class="btn btn-primary w-100 mt-2">Entrar no Admin</button>
</form>
<div class="text-center mt-3 small"><a href="/login">Voltar ao login geral</a></div>
</div></div></div></div>
