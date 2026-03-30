function money(value) {
  return new Intl.NumberFormat('pt-MZ', { style: 'currency', currency: 'MZN', maximumFractionDigits: 2 }).format(Number(value || 0));
}

function renderQuoteSummary(json) {
  const summary = document.getElementById('quote-summary');
  if (!summary || !json?.route || !json?.pricing) return;

  summary.classList.remove('d-none');
  summary.innerHTML = `
    <div class="row g-3">
      <div class="col-md-3 col-6">
        <div class="meta-card">
          <div class="meta-label">Distância</div>
          <div class="meta-value">${json.route.distance_km} km</div>
        </div>
      </div>
      <div class="col-md-3 col-6">
        <div class="meta-card">
          <div class="meta-label">Duração estimada</div>
          <div class="meta-value">${json.route.duration_minutes} min</div>
        </div>
      </div>
      <div class="col-md-3 col-6">
        <div class="meta-card">
          <div class="meta-label">Preço total</div>
          <div class="meta-value">${money(json.pricing.price_total)}</div>
        </div>
      </div>
      <div class="col-md-3 col-6">
        <div class="meta-card">
          <div class="meta-label">Taxa plataforma</div>
          <div class="meta-value">${money(json.pricing.platform_fee)}</div>
        </div>
      </div>
    </div>
    <div class="route-details mt-3">
      <div><strong>Origem:</strong> ${json.pickup.display_name || 'Localização selecionada'}</div>
      <div><strong>Destino:</strong> ${json.dropoff.display_name || '-'}</div>
      <div><strong>Preço base:</strong> ${money(json.pricing.base_price)} · <strong>Distância:</strong> ${money(json.pricing.distance_price)} · <strong>Extra:</strong> ${money(json.pricing.extra_fee)}</div>
    </div>
  `;
}

function setGpsMessage(message, isError = false) {
  const el = document.getElementById('pickup-gps-status');
  if (!el) return;
  el.textContent = message;
  el.classList.toggle('text-danger', isError);
  el.classList.toggle('text-muted', !isError);
}

async function usePickupGps() {
  const latInput = document.getElementById('pickup-lat');
  const lngInput = document.getElementById('pickup-lng');
  const pickupInput = document.getElementById('pickup-address');

  if (!navigator.geolocation) {
    setGpsMessage('GPS não suportado neste dispositivo.', true);
    return;
  }

  setGpsMessage('A obter localização atual...');

  navigator.geolocation.getCurrentPosition((position) => {
    const lat = position.coords.latitude.toFixed(6);
    const lng = position.coords.longitude.toFixed(6);

    if (latInput) latInput.value = lat;
    if (lngInput) lngInput.value = lng;
    if (pickupInput) pickupInput.value = `Minha localização atual (${lat}, ${lng})`;

    setGpsMessage('Localização capturada com sucesso.');
  }, (error) => {
    setGpsMessage(`Não foi possível obter GPS (${error.message}).`, true);
  }, { enableHighAccuracy: true, timeout: 10000 });
}

async function fetchQuote(formId) {
  const form = document.getElementById(formId);
  const data = new FormData(form);
  const feedback = document.getElementById('quote-feedback');
  const createBtn = document.getElementById('create-order-btn');
  const btn = document.getElementById('quote-btn') || form.querySelector('button[type="submit"], button');

  try {
    if (btn) { btn.disabled = true; btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-1"></i>Calculando...'; }
    if (feedback) feedback.textContent = 'Calculando rota real e preço...';

    const response = await fetch('/merchant/orders/quote', { method: 'POST', body: data });
    const json = await response.json();

    if (!response.ok) throw new Error(json.error || 'Falha ao calcular rota.');

    renderQuoteSummary(json);

    if (json.pickup && json.dropoff && json.route) {
      initQuoteMap({
        pickup: { lat: json.pickup.lat, lng: json.pickup.lng, display_name: json.pickup.display_name },
        dropoff: { lat: json.dropoff.lat, lng: json.dropoff.lng, display_name: json.dropoff.display_name },
        route: { polyline: json.route.polyline, distance_km: json.route.distance_km, duration_minutes: json.route.duration_minutes }
      });
    }

    if (createBtn) createBtn.classList.remove('d-none');
    if (feedback) feedback.textContent = 'Cotação pronta. Revise os detalhes e clique em “Submeter pedido”.';
  } catch (e) {
    if (feedback) feedback.textContent = `Erro ao cotar rota: ${e.message}`;
    if (createBtn) createBtn.classList.add('d-none');
  } finally {
    if (btn) { btn.disabled = false; btn.innerHTML = '<i class="fa-solid fa-calculator me-1"></i>Calcular rota e preço'; }
  }
}

async function submitOrder(formId) {
  const form = document.getElementById(formId);
  const data = new FormData(form);
  const feedback = document.getElementById('quote-feedback');
  const btn = document.getElementById('create-order-btn');

  try {
    if (btn) { btn.disabled = true; btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-1"></i>A submeter...'; }
    if (feedback) feedback.textContent = 'A criar pedido...';

    const response = await fetch('/merchant/orders', { method: 'POST', body: data });
    const json = await response.json();

    if (!response.ok) throw new Error(json.error || 'Falha ao criar pedido.');

    const orderId = json.order_id;
    if (feedback) {
      feedback.textContent = `Pedido #${orderId} criado com sucesso. A redirecionar...`;
    }

    window.location.href = `/merchant/orders/${orderId}`;
  } catch (e) {
    if (feedback) feedback.textContent = `Erro ao submeter pedido: ${e.message}`;
  } finally {
    if (btn) { btn.disabled = false; btn.innerHTML = '<i class="fa-solid fa-paper-plane me-1"></i>Submeter pedido'; }
  }
}

async function payOrder(orderId, phone, provider = 'mpesa', feedbackId = null, csrfToken = '') {
  if (!phone) {
    if (feedbackId) {
      const feedback = document.getElementById(feedbackId);
      if (feedback) feedback.textContent = 'Informe um número de telefone para pagamento.';
    }
    return;
  }

  const feedback = feedbackId ? document.getElementById(feedbackId) : null;
  if (feedback) feedback.textContent = 'A iniciar pagamento automático...';

  const data = new FormData();
  data.append('phone', phone);
  data.append('provider', provider);
  if (csrfToken) data.append('_token', csrfToken);

  try {
    const response = await fetch(`/merchant/orders/${orderId}/pay`, { method: 'POST', body: data });
    const json = await response.json();

    if (!response.ok) throw new Error(json.error || 'Falha no pagamento automático.');

    if (feedback) feedback.textContent = 'Pagamento iniciado com sucesso. A atualizar pedido...';
    window.location.href = `/merchant/orders/${orderId}?notice=Pagamento iniciado com sucesso`;
  } catch (e) {
    if (feedback) {
      feedback.textContent = `Erro no pagamento automático: ${e.message}`;
    } else {
      alert(`Erro no pagamento automático: ${e.message}`);
    }
  }
}

async function quickPayOrder(orderId, csrfToken = "") {
  const phone = window.prompt('Digite o telefone para pagamento automático (ex.: 84xxxxxxx):');
  if (!phone) return;
  await payOrder(orderId, phone, 'mpesa', null, csrfToken);
}


async function approveRiderAdmin(riderId, buttonEl = null, csrfToken = "") {
  if (buttonEl) {
    buttonEl.disabled = true;
    buttonEl.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-1"></i>Aprovando...';
  }

  try {
    const form = new FormData();
    if (csrfToken) form.append('_token', csrfToken);
    const response = await fetch(`/admin/riders/${riderId}/approve`, { method: 'POST', body: form });
    const json = await response.json();
    if (!response.ok || !json.approved) {
      throw new Error(json.error || 'Não foi possível aprovar rider.');
    }

    const row = document.getElementById(`pending-rider-${riderId}`);
    if (row) row.remove();
  } catch (e) {
    if (buttonEl) {
      buttonEl.disabled = false;
      buttonEl.innerHTML = '<i class="fa-solid fa-check me-1"></i>Aprovar';
    }
    alert(`Erro ao aprovar rider: ${e.message}`);
  }
}

document.addEventListener('DOMContentLoaded', () => {
  const gpsBtn = document.getElementById('pickup-gps-btn');
  if (gpsBtn) {
    gpsBtn.addEventListener('click', usePickupGps);
  }
});
