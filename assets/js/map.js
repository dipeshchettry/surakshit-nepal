// ============================================================
// Surakshit Nepal — Leaflet.js Map (map.js)
// Layers: weather, flood risk, landslide, earthquake, POI
// ============================================================

'use strict';

const APP_URL_MAP = document.querySelector('meta[name="app-url"]')?.content || '';
let map;
let userMarker = null;
let activeLayers = { weather: true };
let markers  = { earthquake: [], hospitals: [], police: [], fire: [], shelters: [] };
let polygons = { flood: [], landslide: [] };
let weatherMarkers = [];

document.addEventListener('DOMContentLoaded', initMap);

// ----------------------------------------------------------
// Entry point
// ----------------------------------------------------------
function initMap() {
  const lat = window.APP_LAT || 27.7172;
  const lon = window.APP_LON || 85.3240;

  // Clear "Loading map…" text
  document.getElementById('map-container').innerHTML = '';

  map = L.map('map-container').setView([lat, lon], 8);

  // CartoDB Dark Matter tiles (Free, No API Key)
  L.tileLayer('https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png', {
    attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OSM</a> contributors &copy; <a href="https://carto.com/">CARTO</a>',
    subdomains: 'abcd',
    maxZoom: 20
  }).addTo(map);

  // User location marker
  addUserMarker(lat, lon);

  // Load default layers
  loadWeatherLayer(lat, lon);
  loadEarthquakeLayer(lat, lon);
  loadPOILayer('hospital', lat, lon);

  // Update on map move
  map.on('moveend', debounce(() => {
    const center = map.getCenter();
    if (activeLayers.earthquake) loadEarthquakeLayer(center.lat, center.lng);
  }, 1500));
}

// ----------------------------------------------------------
// User Location Marker
// ----------------------------------------------------------
function addUserMarker(lat, lon) {
  if (userMarker) map.removeLayer(userMarker);
  
  // Custom HTML Icon for pulsing effect
  const iconHtml = `
    <div style="position:relative; width:24px; height:24px;">
      <div style="position:absolute; width:100%; height:100%; background:#3b82f6; border-radius:50%; opacity:0.4; animation:pulse 2s infinite;"></div>
      <div style="position:absolute; width:14px; height:14px; background:#3b82f6; border:2px solid #fff; border-radius:50%; top:5px; left:5px;"></div>
    </div>
    <style>@keyframes pulse { 0% {transform:scale(0.95); opacity:0.8;} 100% {transform:scale(2.5); opacity:0;} }</style>
  `;

  const customIcon = L.divIcon({
    html: iconHtml,
    className: 'user-marker-icon',
    iconSize: [24, 24],
    iconAnchor: [12, 12]
  });

  userMarker = L.marker([lat, lon], { icon: customIcon }).addTo(map);
  userMarker.bindTooltip('Your Location', { direction: 'top', offset: [0, -10] });
}

// ----------------------------------------------------------
// Layer toggle (called from HTML)
// ----------------------------------------------------------
window.toggleLayer = function(layer, btn) {
  const isActive = activeLayers[layer];
  activeLayers[layer] = !isActive;

  if (btn) btn.classList.toggle('active', !isActive);

  const center = map.getCenter();
  const lat = window.APP_LAT || center.lat;
  const lon = window.APP_LON || center.lng;

  if (!isActive) {
    // Turn ON
    switch (layer) {
      case 'weather':    loadWeatherLayer(lat, lon);  break;
      case 'flood':      loadFloodLayer();             break;
      case 'landslide':  loadLandslideLayer();         break;
      case 'earthquake': loadEarthquakeLayer(lat, lon); break;
      case 'hospitals':  loadPOILayer('hospital', lat, lon); break;
      case 'police':     loadPOILayer('police', lat, lon);   break;
      case 'fire':       loadPOILayer('fire_station', lat, lon); break;
      case 'shelters':   loadPOILayer('social_facility', lat, lon);  break;
    }
  } else {
    // Turn OFF
    clearLayer(layer);
  }
};

function clearLayer(layer) {
  switch (layer) {
    case 'earthquake':
      markers.earthquake.forEach(m => map.removeLayer(m));
      markers.earthquake = [];
      break;
    case 'hospitals': case 'police': case 'fire': case 'shelters':
      (markers[layer] || []).forEach(m => map.removeLayer(m));
      markers[layer] = [];
      break;
    case 'flood':
      polygons.flood.forEach(p => map.removeLayer(p));
      polygons.flood = [];
      break;
    case 'landslide':
      polygons.landslide.forEach(p => map.removeLayer(p));
      polygons.landslide = [];
      break;
    case 'weather':
      weatherMarkers.forEach(m => map.removeLayer(m));
      weatherMarkers = [];
      break;
  }
}

// ----------------------------------------------------------
// Weather Info Overlay
// ----------------------------------------------------------
async function loadWeatherLayer(lat, lon) {
  try {
    const res  = await fetch(`${APP_URL_MAP}/api/weather.php?lat=${lat}&lon=${lon}`);
    const data = await res.json();
    if (data.error) return;

    const c = data.current;
    const content = `
      <div style="font-family:Inter,sans-serif;padding:4px;min-width:160px;color:#0f172a;">
        <strong style="font-size:1rem;">${escapeHtml(data.location.name)}</strong>
        <div style="font-size:1.5rem;font-weight:800;margin:4px 0;">${Math.round(c.temp)}°C</div>
        <div>${escapeHtml(c.weather_desc)}</div>
        <div style="font-size:0.8rem;color:#64748b;margin-top:6px;">
          💧 ${c.humidity}% · 💨 ${c.wind_speed} m/s
        </div>
      </div>`;

    const customIcon = L.divIcon({
      html: `<div style="font-size:24px; text-shadow:0 0 5px rgba(0,0,0,0.5);">⛅</div>`,
      className: '',
      iconSize: [30, 30],
      iconAnchor: [15, 15]
    });

    const marker = L.marker([lat, lon], { icon: customIcon }).addTo(map);
    marker.bindPopup(content, { autoClose: false, closeOnClick: false }).openPopup();
    weatherMarkers.push(marker);
  } catch (e) {}
}

// ----------------------------------------------------------
// Earthquake Markers
// ----------------------------------------------------------
async function loadEarthquakeLayer(lat, lon) {
  clearLayer('earthquake');
  try {
    const res  = await fetch(`${APP_URL_MAP}/api/alerts.php?lat=${lat}&lon=${lon}`);
    const data = await res.json();
    const eqs  = (data.alerts || []).filter(a => a.type === 'earthquake');

    eqs.forEach(eq => {
      const color = eq.magnitude >= 6 ? '#ef4444' : eq.magnitude >= 4 ? '#f97316' : '#10b981';
      const radius = Math.max(8, Math.min(24, eq.magnitude * 3));

      const marker = L.circleMarker([eq.lat, eq.lon], {
        radius: radius,
        fillColor: color,
        fillOpacity: 0.75,
        color: '#ffffff',
        weight: 1
      }).addTo(map);

      marker.bindPopup(`
        <div style="font-family:Inter,sans-serif;padding:4px;max-width:200px;color:#0f172a;">
          <strong style="font-size:0.95rem;">${escapeHtml(eq.title)}</strong>
          <div style="margin:6px 0;font-size:0.8rem;color:#64748b;">
            📍 ${escapeHtml(eq.affected_area || '')}<br>
            🕐 ${new Date(eq.time).toLocaleString()}<br>
            ⬇️ Depth: ${eq.depth_km ?? '?'} km
          </div>
          <div style="font-size:0.8rem;border-top:1px solid #e2e8f0;padding-top:6px;color:#334155;">
            ${escapeHtml(eq.safety_tips || '')}
          </div>
        </div>`);
      markers.earthquake.push(marker);
    });
  } catch (e) {}
}

// ----------------------------------------------------------
// Flood Risk Zones (Nepal approximate polygons)
// ----------------------------------------------------------
function loadFloodLayer() {
  const floodZones = [
    [[27.0, 83.8], [27.0, 84.5], [27.6, 84.5], [27.6, 83.8]],
    [[26.8, 85.0], [26.8, 86.0], [27.3, 86.0], [27.3, 85.0]]
  ];

  floodZones.forEach(coords => {
    const poly = L.polygon(coords, {
      color: '#3b82f6',
      weight: 2,
      opacity: 0.6,
      fillColor: '#3b82f6',
      fillOpacity: 0.18
    }).addTo(map);

    poly.bindPopup('<div style="padding:4px;color:#0f172a;font-family:Inter,sans-serif;"><strong>⚠️ Flood Risk Zone</strong><p style="font-size:0.8rem;color:#64748b;margin-top:4px;">Historically flood-prone area in Nepal Terai region. Evacuate if water levels rise.</p></div>');
    polygons.flood.push(poly);
  });
}

// ----------------------------------------------------------
// Landslide Risk Zones
// ----------------------------------------------------------
function loadLandslideLayer() {
  const landslideZones = [
    [[27.9, 85.2], [27.9, 85.8], [28.4, 85.8], [28.4, 85.2]],
    [[28.5, 83.5], [28.5, 84.2], [29.0, 84.2], [29.0, 83.5]]
  ];

  landslideZones.forEach(coords => {
    const poly = L.polygon(coords, {
      color: '#f97316',
      weight: 2,
      opacity: 0.7,
      fillColor: '#f97316',
      fillOpacity: 0.20
    }).addTo(map);

    poly.bindPopup('<div style="padding:4px;color:#0f172a;font-family:Inter,sans-serif;"><strong>⚠️ Landslide Risk Zone</strong><p style="font-size:0.8rem;color:#64748b;margin-top:4px;">High landslide risk area. Avoid during heavy monsoon rainfall.</p></div>');
    polygons.landslide.push(poly);
  });
}

// ----------------------------------------------------------
// POI — Hospitals, Police, Fire, Shelters via Overpass API (OSM)
// ----------------------------------------------------------
async function loadPOILayer(amenityType, lat, lon) {
  const layerKey = amenityType === 'hospital' ? 'hospitals'
    : amenityType === 'police'        ? 'police'
    : amenityType === 'fire_station'  ? 'fire'
    : 'shelters';

  clearLayer(layerKey);

  const radius = 25000; // 25km
  const query = `[out:json];node(around:${radius},${lat},${lon})[amenity=${amenityType}];out 15;`;
  const url = `https://overpass-api.de/api/interpreter?data=${encodeURIComponent(query)}`;

  try {
    const res = await fetch(url);
    const data = await res.json();

    const iconMap = {
      hospitals: { char: '🏥', color: '#ef4444' },
      police:    { char: '🛡️', color: '#3b82f6' },
      fire:      { char: '🚒', color: '#f97316' },
      shelters:  { char: '🏠', color: '#10b981' },
    };
    const cfg = iconMap[layerKey] || iconMap.shelters;

    data.elements.forEach(node => {
      const name = node.tags.name || node.tags['name:en'] || (amenityType.charAt(0).toUpperCase() + amenityType.slice(1));
      
      const customIcon = L.divIcon({
        html: `<div style="font-size:20px;text-align:center;">${cfg.char}</div>`,
        className: '',
        iconSize: [24, 24],
        iconAnchor: [12, 12]
      });

      const marker = L.marker([node.lat, node.lon], { icon: customIcon }).addTo(map);
      marker.bindPopup(`
        <div style="font-family:Inter,sans-serif;padding:4px;color:#0f172a;">
          <strong>${escapeHtml(name)}</strong>
          <p style="font-size:0.8rem;color:#64748b;margin-top:4px;">Verified OSM POI</p>
        </div>`);
      
      markers[layerKey].push(marker);
    });
  } catch (e) {
    console.error("Overpass API error:", e);
  }
}

// ----------------------------------------------------------
// Utility
// ----------------------------------------------------------
function debounce(fn, ms) {
  let timer;
  return (...args) => { clearTimeout(timer); timer = setTimeout(() => fn(...args), ms); };
}

function escapeHtml(str) {
  if (!str) return '';
  const map = { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' };
  return str.toString().replace(/[&<>"']/g, m => map[m]);
}
