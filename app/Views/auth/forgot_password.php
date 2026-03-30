<div class="row justify-content-center"><div class="col-lg-5">
<div class="card"><div class="card-body p-4">
<h4 class="fw-bold mb-2">Recuperar senha</h4>
<p class="text-muted">Informe seu email para receber instruções.</p>
<form method="post" action="/forgot-password" class="vstack gap-2">
<input type="hidden" name="_token" value="<?= csrf_token() ?>">
<input class="form-control" type="email" name="email" placeholder="seu@email.com" required>
<button class="btn btn-primary">Enviar link</button>
</form>
</div></div></div></div>
