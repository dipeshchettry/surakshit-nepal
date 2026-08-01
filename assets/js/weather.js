// ============================================================
// Surakshit Nepal — Weather Dashboard JS (weather.js)
// Populates: hero card, hourly, daily, AQI, AI safety panel
// ============================================================

'use strict';

const APP_URL = document.querySelector('meta[name="app-url"]')?.content || '';
let currentWeather = null;

// ----------------------------------------------------------
// Boot — listen for location event or fire if available
// ----------------------------------------------------------
window.addEventListener('sn:location', (e) => {
  const { lat, lon } = e.detail;
  loadWeatherDashboard(lat, lon);
});

// ----------------------------------------------------------
// Main loader
// ----------------------------------------------------------
async function loadWeatherDashboard(lat, lon) {
  try {
    const units  = SN.get('units', 'metric');
    const lang   = SN.get('language', 'en');
    const isNe   = lang === 'ne';

    // Parallel fetch: weather + AQI
    const [weatherRes, aqiRes] = await Promise.all([
      fetch(`${APP_URL}/api/weather.php?lat=${lat}&lon=${lon}&units=${units}`),
      fetch(`${APP_URL}/api/air_quality.php?lat=${lat}&lon=${lon}`)
    ]);

    const weather = await weatherRes.json();
    const aqi     = aqiRes.ok ? await aqiRes.json() : null;

    if (weather.error) throw new Error(weather.error);
    currentWeather = weather;

    renderHero(weather, units);
    renderHourly(weather.hourly || []);
    renderDaily(weather.daily || []);
    renderStats(weather, aqi);
    renderSunriseSunset(weather.current);

    // Update location display
    const locName = document.getElementById('location-name');
    if (locName) {
      locName.textContent = `${weather.location.name}, ${weather.location.country}`;
    }
    const lastUpdated = document.getElementById('last-updated');
    if (lastUpdated) {
      lastUpdated.textContent = `Updated ${new Date().toLocaleTimeString([], { hour:'2-digit', minute:'2-digit' })}`;
    }

    // Load AI safety (non-blocking)
    loadAISafety(lat, lon, weather.current, weather.risk_level);

    // Also load alerts preview
    loadAlertsPreview(lat, lon);

  } catch (err) {
    console.error('Weather load error:', err);
    showWeatherError(err.message);
  }
}

// ----------------------------------------------------------
// Render Hero Card
// ----------------------------------------------------------
function renderHero(data, units) {
  const c = data.current;
  const tempUnit = units === 'imperial' ? '°F' : '°C';
  const windUnit = units === 'imperial' ? 'mph' : 'm/s';

  // Show loaded content
  const skeleton = document.getElementById('hero-skeleton');
  const content  = document.getElementById('hero-content');
  if (skeleton) skeleton.style.display = 'none';
  if (content)  content.style.display = 'block';

  setText('hero-location', `${data.location.name}, ${data.location.country}`);
  setText('hero-temp',    Math.round(c.temp));
  setText('hero-desc',    c.weather_desc);
  setText('hero-feels',  `Feels like ${Math.round(c.feels_like)}${tempUnit}`);
  setText('m-humidity',  `${c.humidity}%`);
  setText('m-wind',      `${c.wind_speed} ${windUnit} ${c.wind_dir}`);
  setText('m-pressure',  `${c.pressure} hPa`);
  setText('m-visibility', `${c.visibility} km`);

  const tempSymbol = document.getElementById('temp-unit-symbol');
  if (tempSymbol) tempSymbol.textContent = tempUnit;

  const heroTime = document.getElementById('hero-time');
  if (heroTime) {
    heroTime.textContent = new Date().toLocaleString('en-US', {
      weekday:'short', month:'short', day:'numeric', hour:'2-digit', minute:'2-digit'
    });
  }

  // Weather icon
  const icon = document.getElementById('hero-icon');
  if (icon) {
    icon.src = c.weather_icon_url || `https://openweathermap.org/img/wn/${c.weather_icon}@2x.png`;
    icon.alt = c.weather_desc;
  }

  // Also fill detail page elements if present
  setText('d-location', `${data.location.name}, ${data.location.country}`);
  setText('d-temp',  Math.round(c.temp));
  setText('d-desc',  c.weather_desc);
  setText('d-feels', `Feels like ${Math.round(c.feels_like)}${tempUnit}`);
  setText('d-humidity',   `${c.humidity}%`);
  setText('d-wind',       `${c.wind_speed} ${windUnit} ${c.wind_dir}`);
  setText('d-pressure',   `${c.pressure} hPa`);
  setText('d-visibility', `${c.visibility} km`);
  setText('d-rain',       `${c.rain_1h} mm`);
  setText('d-clouds',     `${c.clouds}%`);

  const dIcon = document.getElementById('d-icon');
  if (dIcon) { dIcon.src = c.weather_icon_url || `https://openweathermap.org/img/wn/${c.weather_icon}@2x.png`; }

  const dTime = document.getElementById('d-time');
  if (dTime) dTime.textContent = new Date().toLocaleString();
}

// ----------------------------------------------------------
// Hourly Forecast
// ----------------------------------------------------------
function renderHourly(hours) {
  const containers = ['hourly-container', 'd-hourly-container'];
  containers.forEach(id => {
    const el = document.getElementById(id);
    if (!el) return;

    if (!hours.length) {
      el.innerHTML = '<div class="text-muted" style="padding:16px;">No hourly data available.</div>';
      return;
    }

    el.innerHTML = hours.slice(0, 24).map((h, i) => {
      const timeLabel = i === 0 ? 'Now' : new Date(h.time).toLocaleTimeString([], { hour:'2-digit', minute:'2-digit' });
      const icon      = h.icon_url ? `<img src="${h.icon_url}" style="width:32px;height:32px;vertical-align:middle;">` : wmoIcon(h.code || 0);
      const rain      = h.precip_p > 0 ? `<div class="h-rain"><i class="fas fa-droplet"></i> ${h.precip_p}%</div>` : '';
      return `
        <div class="hourly-item ${i === 0 ? 'active' : ''}">
          <div class="h-time">${escapeHtml(timeLabel)}</div>
          <div class="h-icon">${icon}</div>
          <div class="h-temp">${Math.round(h.temp ?? 0)}°</div>
          ${rain}
        </div>`;
    }).join('');
  });
}

// ----------------------------------------------------------
// Daily Forecast
// ----------------------------------------------------------
function renderDaily(days) {
  const containers = ['daily-list', 'd-daily-list'];
  containers.forEach(id => {
    const el = document.getElementById(id);
    if (!el) return;

    if (!days.length) {
      el.innerHTML = '<div style="padding:20px;text-align:center;color:var(--text-muted);">No forecast data.</div>';
      return;
    }

    const allMaxes = days.map(d => d.max || 0);
    const absMax   = Math.max(...allMaxes);
    const absMin   = Math.min(...days.map(d => d.min || 0));

    el.innerHTML = days.map((d, i) => {
      const dayName = i === 0 ? 'Today' : i === 1 ? 'Tomorrow'
        : new Date(d.date).toLocaleDateString('en-US', { weekday:'long' });
      const icon    = d.icon_url ? `<img src="${d.icon_url}" style="width:36px;height:36px;vertical-align:middle;">` : wmoIcon(d.code || 0);
      const barW    = absMax > absMin ? ((d.max - absMin) / (absMax - absMin) * 100).toFixed(0) : 50;

      return `
        <div class="daily-item">
          <div class="day-name">${escapeHtml(dayName)}</div>
          <div class="day-icon">${icon}</div>
          <div class="day-bar" style="flex:1;min-width:60px;max-width:200px;">
            <div class="day-bar-fill" style="width:${barW}%"></div>
          </div>
          <div class="day-temps">
            <span class="day-min">${Math.round(d.min ?? 0)}°</span>
            <span class="day-max">${Math.round(d.max ?? 0)}°</span>
          </div>
        </div>`;
    }).join('');
  });
}

// ----------------------------------------------------------
// Stat Cards
// ----------------------------------------------------------
function renderStats(weather, aqi) {
  const c = weather.current;
  const uv = weather.uv_index ?? 0;

  setText('uv-val',   uv.toFixed(1));
  setText('uv-label', uvLabel(uv));
  setText('rain-val', `${c.rain_1h} mm`);
  setText('cloud-val', `${c.clouds}%`);

  const firstDay = weather.daily?.[0] || {};
  setText('temp-min-val', `${Math.round(firstDay.min ?? c.temp_min)}°`);
  setText('temp-max-val', `${Math.round(firstDay.max ?? c.temp_max)}°`);

  // AQI
  if (aqi) {
    setText('aqi-val',   aqi.aqi);
    setText('aqi-label', aqi.aqi_label);

    // AQI detail on weather.php
    const detail = document.getElementById('aqi-detail');
    if (detail) {
      detail.innerHTML = [
        { label:'AQI',    val:`${aqi.aqi} (${aqi.aqi_label})`, icon:'fa-lungs' },
        { label:'PM2.5',  val:`${aqi.pm2_5} μg/m³`,            icon:'fa-smog' },
        { label:'PM10',   val:`${aqi.pm10} μg/m³`,             icon:'fa-smog' },
        { label:'NO₂',    val:`${aqi.no2} μg/m³`,              icon:'fa-flask' },
        { label:'O₃',     val:`${aqi.o3} μg/m³`,               icon:'fa-flask' },
        { label:'CO',     val:`${aqi.co} μg/m³`,               icon:'fa-flask' },
      ].map(item => `
        <div class="glass-card stat-card glass-card-sm" style="padding:14px;">
          <i class="fas ${item.icon}" style="color:var(--accent-cyan);margin-bottom:6px;font-size:1.2rem;"></i>
          <div class="stat-val" style="font-size:1rem;">${escapeHtml(item.val)}</div>
          <div class="stat-label">${escapeHtml(item.label)}</div>
        </div>`).join('');
    }
  }
}

// ----------------------------------------------------------
// Sunrise / Sunset
// ----------------------------------------------------------
function renderSunriseSunset(c) {
  if (!c) return;
  setText('sunrise-time', formatTime(c.sunrise));
  setText('sunset-time',  formatTime(c.sunset));
  setText('d-sunrise',    formatTime(c.sunrise));
  setText('d-sunset',     formatTime(c.sunset));

  // Sun position arc
  const indicator = document.getElementById('sun-indicator');
  if (indicator) {
    const now = Date.now() / 1000;
    const progress = Math.max(0, Math.min(1, (now - c.sunrise) / (c.sunset - c.sunrise)));
    indicator.style.left = `${progress * 100}%`;
  }
}

// ----------------------------------------------------------
// AI Safety Panel
// ----------------------------------------------------------
async function loadAISafety(lat, lon, current, risk) {
  try {
    const res = await fetch(`${APP_URL}/api/ai_safety.php`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ lat, lon, weather: current, risk_level: risk })
    });
    const data = await res.json();

    const lang    = SN.get('language', 'en');
    const isNe    = lang === 'ne';
    const advice  = data.advice || {};

    // Hide skeleton, show content
    const skeleton = document.getElementById('ai-skeleton');
    const content  = document.getElementById('ai-content');
    if (skeleton) skeleton.style.display = 'none';
    if (content)  content.style.display = 'block';

    // Risk badge
    const badge = document.getElementById('risk-badge');
    const riskText = document.getElementById('risk-text');
    if (badge && riskText) {
      badge.className = `risk-badge risk-${data.risk_level}`;
      riskText.textContent = data.risk_level.charAt(0).toUpperCase() + data.risk_level.slice(1) + ' Risk';
    }

    // Summary
    const summary = isNe ? advice.summary_ne : advice.summary_en;
    setText('ai-summary', summary || '');

    // Tips
    const tips  = isNe ? (advice.advice_ne || []) : (advice.advice_en || []);
    const tipEl = document.getElementById('ai-tips');
    if (tipEl) {
      tipEl.innerHTML = tips.map(t =>
        `<li><i class="fas fa-check-circle"></i> ${escapeHtml(t)}</li>`
      ).join('');
    }

    // Emergency action
    const emergBar  = document.getElementById('emergency-bar');
    const emergText = document.getElementById('emergency-text');
    if (emergBar && advice.emergency_action) {
      emergBar.style.display = 'flex';
      if (emergText) emergText.textContent = advice.emergency_action;
    }

    // Notification if risk is high
    if (data.risk_level === 'high') {
      addNotification('⚠️ High Risk Alert', advice.emergency_action || 'Take immediate safety precautions!', 'danger');
      document.getElementById('notif-dot').style.display = 'block';
    }

  } catch (err) {
    console.warn('AI safety load failed:', err);
    // Silently degrade — hide skeletons
    const skeleton = document.getElementById('ai-skeleton');
    const content  = document.getElementById('ai-content');
    if (skeleton) skeleton.style.display = 'none';
    if (content)  content.innerHTML = '<p style="color:var(--text-muted);font-size:0.85rem;">Safety data unavailable. Add Gemini API key in config.</p>';
  }
}

// ----------------------------------------------------------
// Alerts preview (dashboard only)
// ----------------------------------------------------------
async function loadAlertsPreview(lat, lon) {
  const el = document.getElementById('alerts-preview');
  if (!el) return;

  try {
    const res  = await fetch(`${APP_URL}/api/alerts.php?lat=${lat}&lon=${lon}`);
    const data = await res.json();
    const alerts = (data.alerts || []).slice(0, 3);

    if (!alerts.length) {
      el.innerHTML = `<div style="text-align:center;padding:24px;color:var(--text-muted);">
        <i class="fas fa-shield-check" style="font-size:2rem;display:block;margin-bottom:8px;color:var(--accent-green);opacity:0.6;"></i>
        No active alerts in your area
      </div>`;
      return;
    }

    el.innerHTML = alerts.map(a => `
      <div class="alert-card sev-${a.severity}" style="border-radius:12px;margin-bottom:10px;">
        <div class="alert-header">
          <div class="alert-icon ${alertTypeColor(a.type)}">
            <i class="fas ${alertTypeIcon(a.type)}"></i>
          </div>
          <div>
            <div class="alert-title">${escapeHtml(a.title)}</div>
            <div class="alert-meta">
              <i class="fas fa-location-dot"></i> ${escapeHtml(a.affected_area || 'Nepal')} ·
              <i class="fas fa-clock"></i> ${new Date(a.time).toLocaleDateString()}
            </div>
          </div>
        </div>
        <div class="alert-tips">${escapeHtml((a.safety_tips || '').substring(0, 120))}${(a.safety_tips||'').length > 120 ? '…' : ''}</div>
      </div>
    `).join('');

    // Show notification badge
    if (alerts.some(a => a.severity === 'red' || a.severity === 'orange')) {
      document.getElementById('notif-dot').style.display = 'block';
    }

  } catch (err) {
    el.innerHTML = '<div class="text-muted" style="padding:16px;">Could not load alerts.</div>';
  }
}

// ----------------------------------------------------------
// Refresh button
// ----------------------------------------------------------
window.refreshWeather = function() {
  const loc = SN.getLocation();
  if (loc.lat && loc.lon) {
    loadWeatherDashboard(loc.lat, loc.lon);
  }
};

// ----------------------------------------------------------
// Helpers
// ----------------------------------------------------------
function setText(id, val) {
  const el = document.getElementById(id);
  if (el) el.textContent = val;
}

function uvLabel(uv) {
  if (uv < 3)  return 'Low';
  if (uv < 6)  return 'Moderate';
  if (uv < 8)  return 'High';
  if (uv < 11) return 'Very High';
  return 'Extreme';
}

function showWeatherError(msg) {
  const skeleton = document.getElementById('hero-skeleton');
  if (skeleton) {
    skeleton.innerHTML = `<div style="text-align:center;padding:32px;color:var(--text-muted);">
      <i class="fas fa-cloud-slash" style="font-size:2.5rem;display:block;margin-bottom:12px;opacity:0.4;"></i>
      <p>Could not load weather. Check API key or network.</p>
      <p style="font-size:0.78rem;margin-top:8px;">${escapeHtml(msg)}</p>
      <button onclick="refreshWeather()" style="margin-top:12px;padding:8px 20px;border-radius:10px;border:1px solid var(--glass-border);background:var(--bg-card);color:var(--accent-blue);cursor:pointer;">
        Try Again
      </button>
    </div>`;
  }
}
