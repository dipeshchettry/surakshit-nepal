<?php
// ============================================================
// Surakshit Nepal — Home / Dashboard (index.php)
// ============================================================
$pageTitle   = 'Dashboard';
$pageDesc    = 'Real-time weather, disaster alerts, and AI safety guidance for Nepal.';
$extraScripts = ['assets/js/weather.js', 'assets/js/alerts.js'];

include __DIR__ . '/includes/header.php';
?>

<!-- =========================================================
     SPLASH / WELCOME SCREEN
     ========================================================= -->
<div class="splash-screen" id="splash-screen" role="dialog" aria-modal="true" aria-label="Welcome to Surakshit Nepal">
  <div class="splash-logo" aria-hidden="true" style="background:transparent; box-shadow:none;">
    <img src="<?= APP_URL ?>/assets/images/logo.png" alt="Logo" style="width: 100%; height: 100%; object-fit: contain;">
  </div>

  <h1 class="splash-title">Surakshit Nepal</h1>
  <p class="splash-subtitle">सुरक्षित नेपाल — Your Safety, Our Priority</p>

  <div class="splash-steps">
    <div class="splash-step">
      <div class="step-icon"><i class="fas fa-location-crosshairs"></i></div>
      <div class="step-text">
        <strong>Detect Your Location</strong>
        <span>Get weather for your exact GPS location</span>
      </div>
    </div>
    <div class="splash-step">
      <div class="step-icon" style="background:linear-gradient(135deg,#10b981,#06b6d4)"><i class="fas fa-cloud-sun"></i></div>
      <div class="step-text">
        <strong>Live Weather Data</strong>
        <span>Current conditions, forecasts & AQI</span>
      </div>
    </div>
    <div class="splash-step">
      <div class="step-icon" style="background:linear-gradient(135deg,#ef4444,#f97316)"><i class="fas fa-triangle-exclamation"></i></div>
      <div class="step-text">
        <strong>Disaster Alerts</strong>
        <span>Earthquakes, floods, landslides & more</span>
      </div>
    </div>
  </div>

  <button class="btn-start" id="start-btn" onclick="initApp()">
    <div class="spinner" id="splash-spinner"></div>
    <i class="fas fa-location-arrow" id="start-icon"></i>
    <span id="start-text">Allow Location & Start</span>
  </button>

  <p style="margin-top:16px;font-size:0.78rem;color:var(--text-muted);">
    Your location is only used to fetch weather data and is never stored.
  </p>
</div>

<!-- =========================================================
     MAIN DASHBOARD (hidden until location is detected)
     ========================================================= -->
<div id="dashboard" style="display:none;" class="page-fade">
  <div class="container-main">

    <!-- Location & refresh bar -->
    <div class="d-flex align-center gap-12 mb-24" style="flex-wrap:wrap;">
      <div id="location-display" class="d-flex align-center gap-8">
        <i class="fas fa-location-dot" style="color:var(--accent-blue)"></i>
        <span id="location-name" style="font-weight:600;font-size:1rem;">Detecting location…</span>
      </div>
      <span style="color:var(--text-muted);font-size:0.78rem;" id="last-updated"></span>
      <button onclick="refreshWeather()" id="refresh-btn"
              style="margin-left:auto;background:none;border:1px solid var(--glass-border);color:var(--text-secondary);padding:8px 16px;border-radius:10px;cursor:pointer;font-size:0.82rem;display:flex;align-items:center;gap:6px;transition:var(--transition);"
              onmouseover="this.style.borderColor='var(--accent-blue)';this.style.color='var(--accent-blue)'"
              onmouseout="this.style.borderColor='var(--glass-border)';this.style.color='var(--text-secondary)'">
        <i class="fas fa-rotate-right"></i> Refresh
      </button>
    </div>

    <!-- Dashboard grid -->
    <div class="dashboard-grid">

      <!-- ── Hero Weather Card ── -->
      <div class="col-8">
        <div class="glass-card hero-card" id="hero-weather">
          <!-- Skeleton while loading -->
          <div id="hero-skeleton">
            <div class="skel-text skeleton" style="width:150px"></div>
            <div class="skel-big skeleton"></div>
            <div class="skel-text skeleton"></div>
            <div class="skel-text skeleton" style="width:60%"></div>
          </div>
          <!-- Loaded content -->
          <div id="hero-content" style="display:none">
            <div class="location-info">
              <i class="fas fa-map-pin"></i>
              <span id="hero-location">—</span>
              <span id="hero-time" style="margin-left:auto;font-size:0.78rem;color:var(--text-muted)"></span>
            </div>
            <div class="temp-display">
              <div>
                <div class="temp-value"><span id="hero-temp">—</span><span class="temp-unit" id="temp-unit-symbol">°C</span></div>
                <div class="weather-desc" id="hero-desc">—</div>
                <div class="feels-like" id="hero-feels">Feels like —</div>
              </div>
              <img id="hero-icon" class="weather-icon-large" src="" alt="Weather icon">
            </div>
            <div class="weather-meta">
              <div class="meta-item" data-tooltip="Relative Humidity">
                <i class="fas fa-droplet"></i>
                <span class="meta-val" id="m-humidity">—</span>
                <span class="meta-label">Humidity</span>
              </div>
              <div class="meta-item" data-tooltip="Wind Speed & Direction">
                <i class="fas fa-wind"></i>
                <span class="meta-val" id="m-wind">—</span>
                <span class="meta-label">Wind</span>
              </div>
              <div class="meta-item" data-tooltip="Atmospheric Pressure">
                <i class="fas fa-gauge-high"></i>
                <span class="meta-val" id="m-pressure">—</span>
                <span class="meta-label">Pressure</span>
              </div>
              <div class="meta-item" data-tooltip="Visibility Distance">
                <i class="fas fa-eye"></i>
                <span class="meta-val" id="m-visibility">—</span>
                <span class="meta-label">Visibility</span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- ── Stat Cards column ── -->
      <div class="col-4" style="display:flex;flex-direction:column;gap:16px;">
        <!-- Sunrise / Sunset -->
        <div class="glass-card stat-card glass-card-sm">
          <div class="d-flex gap-16" style="justify-content:space-around;">
            <div style="text-align:center;">
              <i class="fas fa-sun" style="font-size:1.5rem;color:#f59e0b;"></i>
              <div style="font-size:1.1rem;font-weight:800;margin-top:6px;" id="sunrise-time">—</div>
              <div class="text-muted">Sunrise</div>
            </div>
            <div style="width:1px;background:var(--glass-border);"></div>
            <div style="text-align:center;">
              <i class="fas fa-moon" style="font-size:1.5rem;color:#8b5cf6;"></i>
              <div style="font-size:1.1rem;font-weight:800;margin-top:6px;" id="sunset-time">—</div>
              <div class="text-muted">Sunset</div>
            </div>
          </div>
        </div>

        <!-- UV Index -->
        <div class="glass-card stat-card glass-card-sm">
          <div class="stat-icon icon-yellow"><i class="fas fa-sun"></i></div>
          <div class="stat-val" id="uv-val">—</div>
          <div class="stat-label">UV Index</div>
          <div class="stat-sub" id="uv-label">—</div>
        </div>

        <!-- Air Quality -->
        <div class="glass-card stat-card glass-card-sm">
          <div class="stat-icon icon-green"><i class="fas fa-lungs"></i></div>
          <div class="stat-val" id="aqi-val">—</div>
          <div class="stat-label">Air Quality</div>
          <div class="stat-sub" id="aqi-label">—</div>
        </div>
      </div>

      <!-- ── Hourly Forecast ── -->
      <div class="col-12">
        <div class="glass-card" style="padding:20px 20px 8px;">
          <div class="section-header">
            <h2 class="section-title"><i class="fas fa-clock"></i> Hourly Forecast</h2>
            <span class="text-small text-muted">Next 24 hours</span>
          </div>
          <div class="hourly-scroll" id="hourly-scroll">
            <div class="hourly-container" id="hourly-container">
              <!-- Filled by JS -->
              <div class="text-muted" style="padding:16px;">Loading…</div>
            </div>
          </div>
        </div>
      </div>

      <!-- ── AI Safety Panel ── -->
      <div class="col-4">
        <div class="glass-card safety-panel" id="ai-panel">
          <div class="section-header mb-16">
            <h2 class="section-title"><i class="fas fa-robot"></i> AI Safety</h2>
            <a href="<?= APP_URL ?>/alerts.php" class="section-link">View all <i class="fas fa-arrow-right"></i></a>
          </div>
          <div id="ai-skeleton">
            <div class="skel-text skeleton" style="width:100px;height:24px;border-radius:20px;"></div>
            <div class="skel-text skeleton mt-16"></div>
            <div class="skel-text skeleton"></div>
            <div class="skel-text skeleton" style="width:80%"></div>
          </div>
          <div id="ai-content" style="display:none">
            <div class="risk-badge" id="risk-badge">
              <i class="fas fa-shield-halved"></i>
              <span id="risk-text">Safe</span>
            </div>
            <div class="summary-text" id="ai-summary">—</div>
            <ul class="safety-tips-list" id="ai-tips"></ul>
            <div class="emergency-action-bar" id="emergency-bar" style="display:none">
              <i class="fas fa-siren-on"></i>
              <span id="emergency-text"></span>
            </div>
          </div>
        </div>
      </div>

      <!-- ── 7-Day Forecast ── -->
      <div class="col-8">
        <div class="glass-card" style="overflow:hidden;">
          <div style="padding:20px 20px 12px;">
            <div class="section-header">
              <h2 class="section-title"><i class="fas fa-calendar-week"></i> 7-Day Forecast</h2>
              <a href="<?= APP_URL ?>/weather.php" class="section-link">Details <i class="fas fa-arrow-right"></i></a>
            </div>
          </div>
          <div class="daily-list" id="daily-list">
            <div style="padding:24px;text-align:center;color:var(--text-muted);">Loading forecast…</div>
          </div>
        </div>
      </div>

      <!-- ── Active Alerts ── -->
      <div class="col-6">
        <div class="glass-card" style="padding:20px;">
          <div class="section-header mb-16">
            <h2 class="section-title"><i class="fas fa-triangle-exclamation"></i> Active Alerts</h2>
            <a href="<?= APP_URL ?>/alerts.php" class="section-link">All <i class="fas fa-arrow-right"></i></a>
          </div>
          <div id="alerts-preview">
            <div class="text-muted" style="text-align:center;padding:24px;">
              <i class="fas fa-satellite-dish" style="font-size:2rem;display:block;margin-bottom:8px;opacity:0.3"></i>
              Fetching alerts…
            </div>
          </div>
        </div>
      </div>

      <!-- ── Additional Stats ── -->
      <div class="col-6" style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
        <div class="glass-card stat-card glass-card-sm">
          <div class="stat-icon icon-blue"><i class="fas fa-cloud-rain"></i></div>
          <div class="stat-val" id="rain-val">0 mm</div>
          <div class="stat-label">Rain (1h)</div>
        </div>
        <div class="glass-card stat-card glass-card-sm">
          <div class="stat-icon icon-cyan"><i class="fas fa-cloud"></i></div>
          <div class="stat-val" id="cloud-val">—</div>
          <div class="stat-label">Cloud Cover</div>
        </div>
        <div class="glass-card stat-card glass-card-sm">
          <div class="stat-icon icon-purple"><i class="fas fa-temperature-low"></i></div>
          <div class="stat-val" id="temp-min-val">—</div>
          <div class="stat-label">Min Today</div>
        </div>
        <div class="glass-card stat-card glass-card-sm">
          <div class="stat-icon icon-orange"><i class="fas fa-temperature-high"></i></div>
          <div class="stat-val" id="temp-max-val">—</div>
          <div class="stat-label">Max Today</div>
        </div>
      </div>

    </div><!-- .dashboard-grid -->
  </div><!-- .container-main -->
</div><!-- #dashboard -->

<?php include __DIR__ . '/includes/footer.php'; ?>
