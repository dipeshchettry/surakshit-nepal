<?php
$pageTitle   = 'Interactive Map';
$pageDesc    = 'View real-time weather layers, flood risk, earthquake locations, and emergency services on an interactive map of Nepal.';
$extraScripts = ['assets/js/map.js'];
include __DIR__ . '/includes/header.php';
?>

<div class="container-main page-fade">
  <div class="page-hero" style="padding-top:20px;padding-bottom:16px;">
    <h1><i class="fas fa-map-location-dot" style="color:var(--accent-blue)"></i> Interactive Map</h1>
    <p>Real-time weather overlays, risk zones & emergency services across Nepal</p>
  </div>

  <div class="glass-card mb-16" style="overflow:hidden;padding:0;">
    <!-- Layer Controls -->
    <div class="map-controls">
      <button class="map-layer-btn active" id="layer-weather" onclick="toggleLayer('weather',this)">
        <i class="fas fa-cloud-sun"></i> Weather
      </button>
      <button class="map-layer-btn" id="layer-flood" onclick="toggleLayer('flood',this)">
        <i class="fas fa-water"></i> Flood Risk
      </button>
      <button class="map-layer-btn" id="layer-landslide" onclick="toggleLayer('landslide',this)">
        <i class="fas fa-mountain"></i> Landslide
      </button>
      <button class="map-layer-btn" id="layer-earthquake" onclick="toggleLayer('earthquake',this)">
        <i class="fas fa-circle-radiation"></i> Earthquakes
      </button>
      <button class="map-layer-btn" id="layer-hospitals" onclick="toggleLayer('hospitals',this)">
        <i class="fas fa-hospital"></i> Hospitals
      </button>
      <button class="map-layer-btn" id="layer-police" onclick="toggleLayer('police',this)">
        <i class="fas fa-shield"></i> Police
      </button>
      <button class="map-layer-btn" id="layer-fire" onclick="toggleLayer('fire',this)">
        <i class="fas fa-fire-extinguisher"></i> Fire Stations
      </button>
      <button class="map-layer-btn" id="layer-shelters" onclick="toggleLayer('shelters',this)">
        <i class="fas fa-house-chimney-medical"></i> Shelters
      </button>
    </div>

    <!-- Map container -->
    <div id="map-container">
      <div style="height:100%;display:grid;place-items:center;color:var(--text-muted);">
        <div style="text-align:center;">
          <i class="fas fa-map-location-dot" style="font-size:3rem;margin-bottom:12px;opacity:0.3;display:block;"></i>
          <p>Loading map…</p>
        </div>
      </div>
    </div>
  </div>

  <!-- Map legend -->
  <div class="glass-card" style="padding:16px 20px;">
    <h3 style="font-size:0.85rem;font-weight:700;text-transform:uppercase;letter-spacing:1px;color:var(--text-muted);margin-bottom:12px;">Legend</h3>
    <div style="display:flex;flex-wrap:wrap;gap:16px;font-size:0.8rem;">
      <span><i class="fas fa-location-dot" style="color:var(--accent-blue)"></i> Your Location</span>
      <span><i class="fas fa-circle" style="color:#ef4444"></i> Earthquake (M5+)</span>
      <span><i class="fas fa-circle" style="color:#f97316"></i> Earthquake (M3-5)</span>
      <span><i class="fas fa-circle" style="color:#10b981"></i> Earthquake (M&lt;3)</span>
      <span><i class="fas fa-square" style="color:rgba(59,130,246,0.4)"></i> Flood Risk</span>
      <span><i class="fas fa-square" style="color:rgba(249,115,22,0.4)"></i> Landslide Risk</span>
      <span><i class="fas fa-plus-square" style="color:#ef4444"></i> Hospital</span>
      <span><i class="fas fa-shield" style="color:var(--accent-blue)"></i> Police</span>
    </div>
  </div>
</div>

<!-- Leaflet JS & CSS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin=""/>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin=""></script>
<script>
  window.APP_LAT = parseFloat(localStorage.getItem('sn_lat') || '27.7172');
  window.APP_LON = parseFloat(localStorage.getItem('sn_lon') || '85.3240');
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
