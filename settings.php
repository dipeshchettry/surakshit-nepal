<?php
$pageTitle = 'Settings';
$pageDesc  = 'Customize Surakshit Nepal — language, theme, units, and notification preferences.';
include __DIR__ . '/includes/header.php';
?>

<div class="container-main page-fade" style="max-width:700px;">
  <div class="page-hero">
    <h1><i class="fas fa-gear" style="color:var(--accent-blue)"></i> Settings</h1>
    <p>Customize your experience — changes are saved automatically</p>
  </div>

  <!-- Appearance -->
  <div class="settings-section">
    <h3>Appearance</h3>

    <div class="setting-row">
      <div class="setting-label">
        <div class="stat-icon icon-purple" style="width:36px;height:36px;"><i class="fas fa-moon"></i></div>
        <div>
          <div class="setting-name">Theme</div>
          <div class="setting-desc">Choose between dark and light mode</div>
        </div>
      </div>
      <div class="segmented" id="theme-segmented">
        <button id="theme-dark-btn" class="active" onclick="setSetting('theme','dark')">
          <i class="fas fa-moon"></i> Dark
        </button>
        <button id="theme-light-btn" onclick="setSetting('theme','light')">
          <i class="fas fa-sun"></i> Light
        </button>
      </div>
    </div>

    <div class="setting-row">
      <div class="setting-label">
        <div class="stat-icon icon-cyan" style="width:36px;height:36px;"><i class="fas fa-language"></i></div>
        <div>
          <div class="setting-name">Language</div>
          <div class="setting-desc">Interface and AI advice language</div>
        </div>
      </div>
      <div class="segmented" id="lang-segmented">
        <button id="lang-en-btn" class="active" onclick="setSetting('language','en')">🇬🇧 English</button>
        <button id="lang-ne-btn" onclick="setSetting('language','ne')">🇳🇵 नेपाली</button>
      </div>
    </div>
  </div>

  <!-- Units -->
  <div class="settings-section">
    <h3>Measurement Units</h3>

    <div class="setting-row">
      <div class="setting-label">
        <div class="stat-icon icon-orange" style="width:36px;height:36px;"><i class="fas fa-temperature-half"></i></div>
        <div>
          <div class="setting-name">Temperature</div>
          <div class="setting-desc">Celsius or Fahrenheit</div>
        </div>
      </div>
      <div class="segmented" id="units-segmented">
        <button id="units-metric-btn" class="active" onclick="setSetting('units','metric')">°C Metric</button>
        <button id="units-imperial-btn" onclick="setSetting('units','imperial')">°F Imperial</button>
      </div>
    </div>
  </div>

  <!-- Notifications -->
  <div class="settings-section">
    <h3>Notifications</h3>

    <div class="setting-row">
      <div class="setting-label">
        <div class="stat-icon icon-blue" style="width:36px;height:36px;"><i class="fas fa-bell"></i></div>
        <div>
          <div class="setting-name">Push Notifications</div>
          <div class="setting-desc">Receive disaster alerts in browser</div>
        </div>
      </div>
      <label class="toggle-switch">
        <input type="checkbox" id="notif-toggle" onchange="setSetting('notifications',this.checked)">
        <span class="toggle-slider"></span>
      </label>
    </div>

    <div class="setting-row">
      <div class="setting-label">
        <div class="stat-icon icon-red" style="width:36px;height:36px;"><i class="fas fa-circle-radiation"></i></div>
        <div>
          <div class="setting-name">Earthquake Alerts</div>
          <div class="setting-desc">Alerts for M3.5+ earthquakes near Nepal</div>
        </div>
      </div>
      <label class="toggle-switch">
        <input type="checkbox" id="eq-toggle" onchange="setSetting('eq_alerts',this.checked)" checked>
        <span class="toggle-slider"></span>
      </label>
    </div>

    <div class="setting-row">
      <div class="setting-label">
        <div class="stat-icon icon-blue" style="width:36px;height:36px;"><i class="fas fa-water"></i></div>
        <div>
          <div class="setting-name">Flood & Landslide Alerts</div>
          <div class="setting-desc">Weather-based flood/landslide warnings</div>
        </div>
      </div>
      <label class="toggle-switch">
        <input type="checkbox" id="flood-toggle" onchange="setSetting('flood_alerts',this.checked)" checked>
        <span class="toggle-slider"></span>
      </label>
    </div>
  </div>

  <!-- Data & Privacy -->
  <div class="settings-section">
    <h3>Data & Privacy</h3>

    <div class="setting-row">
      <div class="setting-label">
        <div class="stat-icon icon-green" style="width:36px;height:36px;"><i class="fas fa-location-dot"></i></div>
        <div>
          <div class="setting-name">Saved Location</div>
          <div class="setting-desc" id="saved-location-desc">No location saved</div>
        </div>
      </div>
      <button onclick="clearLocation()" style="padding:8px 14px;border-radius:10px;border:1px solid var(--glass-border);background:var(--bg-card);color:var(--text-secondary);cursor:pointer;font-size:0.8rem;">
        Clear
      </button>
    </div>

    <div class="setting-row">
      <div class="setting-label">
        <div class="stat-icon icon-red" style="width:36px;height:36px;"><i class="fas fa-trash"></i></div>
        <div>
          <div class="setting-name">Clear All Data</div>
          <div class="setting-desc">Remove all settings and cached data</div>
        </div>
      </div>
      <button onclick="clearAllData()" style="padding:8px 14px;border-radius:10px;border:1px solid rgba(239,68,68,0.4);background:rgba(239,68,68,0.08);color:var(--accent-red);cursor:pointer;font-size:0.8rem;font-weight:600;">
        Clear All
      </button>
    </div>
  </div>

  <!-- About -->
  <div class="settings-section">
    <h3>About</h3>
    <div class="glass-card" style="padding:20px;">
      <div class="d-flex align-center gap-12 mb-16">
        <div style="width:48px;height:48px;border-radius:14px;background:var(--gradient-accent);display:grid;place-items:center;font-size:22px;color:white;">
          <i class="fas fa-shield-halved"></i>
        </div>
        <div>
          <strong style="font-size:1rem;">Surakshit Nepal v1.0</strong>
          <div class="text-muted" style="font-size:0.78rem;">AI-powered Weather & Disaster Early Warning</div>
        </div>
      </div>
      <p style="font-size:0.82rem;color:var(--text-secondary);line-height:1.7;">
        Surakshit Nepal aggregates weather data from multiple trusted sources and uses Google Gemini AI to provide
        safety guidance tailored to conditions in Nepal. Disaster alerts are sourced from USGS, GDACS, and ReliefWeb.
        No account or personal data is required.
      </p>
      <div class="divider"></div>
      <div style="font-size:0.78rem;color:var(--text-muted);">
        Data: OpenWeatherMap · Open-Meteo · USGS · GDACS · ReliefWeb<br>
        AI: Google Gemini 1.5 Flash<br>
        Maps: Google Maps Platform
      </div>
    </div>
  </div>
</div>

<script>
// ---- Settings Manager ----
const SNSettings = {
  defaults: { theme:'dark', language:'en', units:'metric', notifications:false, eq_alerts:true, flood_alerts:true },

  get(key) {
    const s = JSON.parse(localStorage.getItem('sn_settings') || '{}');
    return s[key] ?? this.defaults[key];
  },

  set(key, value) {
    const s = JSON.parse(localStorage.getItem('sn_settings') || '{}');
    s[key] = value;
    localStorage.setItem('sn_settings', JSON.stringify(s));
  },

  init() {
    // Theme
    const theme = this.get('theme');
    document.documentElement.setAttribute('data-theme', theme);
    document.getElementById('theme-dark-btn').classList.toggle('active', theme === 'dark');
    document.getElementById('theme-light-btn').classList.toggle('active', theme === 'light');

    // Language
    const lang = this.get('language');
    document.getElementById('lang-en-btn').classList.toggle('active', lang === 'en');
    document.getElementById('lang-ne-btn').classList.toggle('active', lang === 'ne');

    // Units
    const units = this.get('units');
    document.getElementById('units-metric-btn').classList.toggle('active', units === 'metric');
    document.getElementById('units-imperial-btn').classList.toggle('active', units === 'imperial');

    // Notifications
    document.getElementById('notif-toggle').checked = this.get('notifications');
    document.getElementById('eq-toggle').checked    = this.get('eq_alerts');
    document.getElementById('flood-toggle').checked = this.get('flood_alerts');

    // Location
    const lat = localStorage.getItem('sn_lat');
    const lon = localStorage.getItem('sn_lon');
    const locDesc = document.getElementById('saved-location-desc');
    if (lat && lon) {
      locDesc.textContent = `Lat: ${parseFloat(lat).toFixed(4)}, Lon: ${parseFloat(lon).toFixed(4)}`;
    }
  }
};

function setSetting(key, value) {
  SNSettings.set(key, value);
  SNSettings.init();

  if (key === 'theme') {
    document.documentElement.setAttribute('data-theme', value);
  }
  if (key === 'language') {
    document.body.classList.toggle('lang-ne', value === 'ne');
    document.querySelectorAll('.nav-label-en').forEach(el => el.style.display = value === 'ne' ? 'none' : '');
    document.querySelectorAll('.nav-label-ne').forEach(el => el.style.display = value === 'ne' ? '' : 'none');
    document.getElementById('lang-text').textContent = value === 'ne' ? 'EN' : 'NE';
  }
  if (key === 'notifications' && value) {
    if (typeof OneSignal !== 'undefined') {
      OneSignal.Notifications.requestPermission();
    } else {
      Notification.requestPermission();
    }
  }
}

function clearLocation() {
  localStorage.removeItem('sn_lat');
  localStorage.removeItem('sn_lon');
  document.getElementById('saved-location-desc').textContent = 'No location saved';
  alert('Location cleared. Refresh the dashboard to re-detect.');
}

function clearAllData() {
  if (confirm('Clear ALL settings and cached data? You will need to allow location again.')) {
    localStorage.clear();
    sessionStorage.clear();
    alert('All data cleared. Redirecting to home…');
    window.location.href = '<?= APP_URL ?>/index.php';
  }
}

// Init on load
SNSettings.init();
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
