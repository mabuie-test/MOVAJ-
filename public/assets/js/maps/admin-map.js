window.initAdminMap = function(activeOrders){
  const el = document.getElementById('admin-map');
  if (!el || !window.L) return;
  const map = L.map(el).setView([-25.97, 32.58], 11);
  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19 }).addTo(map);
  (activeOrders || []).forEach(o => {
    L.circleMarker([o.pickup_lat, o.pickup_lng], {radius:5,color:'#198754'}).addTo(map);
    L.circleMarker([o.dropoff_lat, o.dropoff_lng], {radius:5,color:'#dc3545'}).addTo(map);
  });
};
