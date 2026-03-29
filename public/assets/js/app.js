async function fetchQuote(formId) {
  const form = document.getElementById(formId);
  const data = new FormData(form);
  const output = document.getElementById('quote-output');

  try {
    const response = await fetch('/merchant/orders/quote', { method: 'POST', body: data });
    const json = await response.json();
    output.textContent = JSON.stringify(json, null, 2);

    if (json.pickup && json.dropoff && json.route) {
      initQuoteMap({
        pickup: { lat: json.pickup.lat, lng: json.pickup.lng },
        dropoff: { lat: json.dropoff.lat, lng: json.dropoff.lng },
        route: { polyline: json.route.polyline, distance_km: json.route.distance_km, duration_minutes: json.route.duration_minutes }
      });
    }
  } catch (e) {
    output.textContent = `Erro ao cotar rota: ${e.message}`;
  }
}
