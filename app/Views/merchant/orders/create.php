<div class="card"><div class="card-body">
<h4 class="fw-bold mb-3"><i class="fa-solid fa-route me-2"></i>Criar Pedido de Entrega</h4>
<form id="quote-form" onsubmit="event.preventDefault(); fetchQuote('quote-form');">
<input type="hidden" name="_token" value="<?= csrf_token() ?>">
<div class="row g-3">
<div class="col-md-6"><label class="form-label"><i class="fa-solid fa-city me-1"></i>Cidade</label><input class="form-control" name="city" placeholder="Maputo / Matola" required></div>
<div class="col-md-6"><label class="form-label">Zona</label><input class="form-control" name="zone" placeholder="Zona operacional"></div>
<div class="col-md-6"><label class="form-label"><i class="fa-solid fa-location-dot me-1"></i>Origem</label><input class="form-control" name="pickup_address" required></div>
<div class="col-md-6"><label class="form-label"><i class="fa-solid fa-flag-checkered me-1"></i>Destino</label><input class="form-control" name="dropoff_address" required></div>
<div class="col-md-6"><label class="form-label">Contato recolha</label><input class="form-control" name="pickup_contact_name" required></div>
<div class="col-md-6"><label class="form-label">Telefone recolha</label><input class="form-control" name="pickup_contact_phone" required></div>
<div class="col-md-6"><label class="form-label">Contato entrega</label><input class="form-control" name="dropoff_contact_name" required></div>
<div class="col-md-6"><label class="form-label">Telefone entrega</label><input class="form-control" name="dropoff_contact_phone" required></div>
<div class="col-md-6"><label class="form-label"><i class="fa-solid fa-box-open me-1"></i>Tipo de pacote</label><select class="form-select" name="package_type"><option value="normal">Normal</option><option value="fragile">Frágil</option><option value="express">Express</option></select></div>
<div class="col-md-6"><label class="form-label">Descrição</label><input class="form-control" name="package_description" required></div>
</div>
<button class="btn btn-primary mt-3"><i class="fa-solid fa-calculator me-1"></i>Calcular rota e preço</button>
</form>
<div class="map-panel mt-3">
  <div class="d-flex justify-content-between align-items-center mb-2"><strong><i class="fa-solid fa-map me-1"></i>Preview da rota</strong><span class="small text-muted">Rota real OSRM</span></div>
  <div id="quote-map"></div>
</div>
<pre id="quote-output" class="mt-3 p-3 bg-light border rounded"></pre>
</div></div>
