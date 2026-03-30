window.MovaMap = {
  decodePolyline(encoded) {
    if (!encoded) return [];
    let points = [], index = 0, lat = 0, lng = 0;
    while (index < encoded.length) {
      let b, shift = 0, result = 0;
      do { b = encoded.charCodeAt(index++) - 63; result |= (b & 0x1f) << shift; shift += 5; } while (b >= 0x20);
      const dlat = ((result & 1) ? ~(result >> 1) : (result >> 1)); lat += dlat;
      shift = 0; result = 0;
      do { b = encoded.charCodeAt(index++) - 63; result |= (b & 0x1f) << shift; shift += 5; } while (b >= 0x20);
      const dlng = ((result & 1) ? ~(result >> 1) : (result >> 1)); lng += dlng;
      points.push([lat / 1e5, lng / 1e5]);
    }
    return points;
  },
  drawMap(elId, payload, options = {}) {
    const el = document.getElementById(elId);
    if (!el || !window.L || !payload) return;

    window.__movaMaps = window.__movaMaps || {};
    if (window.__movaMaps[elId]) {
      window.__movaMaps[elId].remove();
    }

    const map = L.map(el).setView([payload.pickup.lat, payload.pickup.lng], 13);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19 }).addTo(map);

    const pickupDetails = payload.pickup.display_name || `${payload.pickup.lat}, ${payload.pickup.lng}`;
    const dropoffDetails = payload.dropoff.display_name || `${payload.dropoff.lat}, ${payload.dropoff.lng}`;

    L.marker([payload.pickup.lat, payload.pickup.lng]).addTo(map)
      .bindPopup(`<strong>Origem</strong><br>${pickupDetails}`);

    L.marker([payload.dropoff.lat, payload.dropoff.lng]).addTo(map)
      .bindPopup(`<strong>Destino</strong><br>${dropoffDetails}`);

    const line = payload.route?.polyline
      ? this.decodePolyline(payload.route.polyline)
      : [[payload.pickup.lat, payload.pickup.lng], [payload.dropoff.lat, payload.dropoff.lng]];

    const routeLayer = L.polyline(line, { color: '#0d6efd', weight: 5, opacity: 0.85 }).addTo(map);
    routeLayer.bindPopup(`Distância: ${payload.route?.distance_km ?? '-'} km<br>Duração: ${payload.route?.duration_minutes ?? '-'} min`);
    map.fitBounds(routeLayer.getBounds(), { padding: [20, 20] });

    if (payload.rider?.lat && payload.rider?.lng) {
      const riderMarker = L.marker([payload.rider.lat, payload.rider.lng]).addTo(map).bindPopup('Rider');
      if (options.liveUrl && options.pollSeconds) {
        setInterval(async () => {
          const res = await fetch(options.liveUrl);
          const json = await res.json();
          if (json.rider_location?.lat) {
            riderMarker.setLatLng([json.rider_location.lat, json.rider_location.lng]);
          }
        }, options.pollSeconds * 1000);
      }
    }

    window.__movaMaps[elId] = map;
    return map;
  }
};
