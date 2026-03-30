<div class="row justify-content-center"><div class="col-lg-5">
<div class="card"><div class="card-body p-4">
<h4 class="fw-bold mb-2">Redefinir senha</h4>
<?php if (!empty($error)): ?><div class="alert alert-danger"><?= e((string)$error) ?></div><?php endif; ?>
<form method="post" action="/reset-password" class="vstack gap-2">
<input type="hidden" name="_token" value="<?= csrf_token() ?>">
<input type="hidden" name="token" value="<?= e((string)($token ?? '')) ?>">
<input class="form-control" type="password" name="password" placeholder="Nova senha" required>
<input class="form-control" type="password" name="password_confirmation" placeholder="Confirmar senha" required>
<button class="btn btn-primary">Atualizar senha</button>
</form>
</div></div></div></div>
