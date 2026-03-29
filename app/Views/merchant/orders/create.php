<div class="card"><div class="card-body">
<h3>Criar Pedido de Entrega</h3>
<form id="quote-form" onsubmit="event.preventDefault(); fetchQuote('quote-form');">
<input type="hidden" name="_token" value="<?= csrf_token() ?>">
<div class="row g-2">
<div class="col-md-6"><input class="form-control" name="city" placeholder="Cidade (Maputo/Matola)" required></div>
<div class="col-md-6"><input class="form-control" name="zone" placeholder="Zona"></div>
<div class="col-md-6"><input class="form-control" name="pickup_address" placeholder="Endereço de recolha" required></div>
<div class="col-md-6"><input class="form-control" name="dropoff_address" placeholder="Endereço de entrega" required></div>
<div class="col-md-6"><input class="form-control" name="pickup_contact_name" placeholder="Contato recolha" required></div>
<div class="col-md-6"><input class="form-control" name="pickup_contact_phone" placeholder="Telefone recolha" required></div>
<div class="col-md-6"><input class="form-control" name="dropoff_contact_name" placeholder="Contato entrega" required></div>
<div class="col-md-6"><input class="form-control" name="dropoff_contact_phone" placeholder="Telefone entrega" required></div>
<div class="col-md-6"><select class="form-select" name="package_type"><option value="normal">Normal</option><option value="fragile">Frágil</option><option value="express">Express</option></select></div>
<div class="col-md-6"><input class="form-control" name="package_description" placeholder="Descrição do pacote" required></div>
</div>
<button class="btn btn-success mt-3">Simular Preço por Rota Real</button>
</form>
<div id="quote-map" class="mt-3"></div>
<pre id="quote-output" class="mt-3 p-3 bg-light border rounded"></pre>
</div></div>
