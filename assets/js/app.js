// ============================================================
// Surakshit Nepal — Core App (app.js)
// Handles: theme, language, settings, nav, geolocation init
// ============================================================

'use strict';

// ----------------------------------------------------------
// Settings
// ----------------------------------------------------------
const SN = {
  settings: null,
  lat: null,
  lon: null,

  getSettings() {
    if (!this.settings) {
      this.settings = JSON.parse(localStorage.getItem('sn_settings') || '{}');
    }
    return this.settings;
  },

  get(key, def = null) {
    const s = this.getSettings();
    const defaults = { theme:'dark', language:'en', units:'metric', notifications:false, eq_alerts:true, flood_alerts:true };
    return s[key] !== undefined ? s[key] : (def ?? defaults[key]);
  },

  set(key, value) {
    const s = this.getSettings();
    s[key] = value;
    localStorage.setItem('sn_settings', JSON.stringify(s));
    this.settings = s;
  },

  getLocation() {
    this.lat = parseFloat(localStorage.getItem('sn_lat') || '0');
    this.lon = parseFloat(localStorage.getItem('sn_lon') || '0');
    return { lat: this.lat, lon: this.lon };
  },

  saveLocation(lat, lon) {
    localStorage.setItem('sn_lat', lat);
    localStorage.setItem('sn_lon', lon);
    this.lat = lat;
    this.lon = lon;
  },

  get appUrl() {
    const meta = document.querySelector('meta[name="app-url"]');
    return meta ? meta.content : '';
  }
};

// ----------------------------------------------------------
// Apply stored theme on page load (before paint)
// ----------------------------------------------------------
(function() {
  const theme = SN.get('theme', 'dark');
  document.documentElement.setAttribute('data-theme', theme);
  if (SN.get('language') === 'ne') {
    document.body && document.body.classList.add('lang-ne');
  }
})();

// ----------------------------------------------------------
// DOM Ready
// ----------------------------------------------------------
document.addEventListener('DOMContentLoaded', () => {
  initThemeToggle();
  initLangToggle();
  initMobileNav();
  initScrollTop();
  initNotifDropdown();
  applyLanguage(SN.get('language'));
  updateThemeIcon(SN.get('theme'));
});

// ----------------------------------------------------------
// Theme
// ----------------------------------------------------------
function initThemeToggle() {
  const btn = document.getElementById('theme-toggle');
  if (!btn) return;
  btn.addEventListener('click', () => {
    const current = document.documentElement.getAttribute('data-theme') || 'dark';
    const next    = current === 'dark' ? 'light' : 'dark';
    document.documentElement.setAttribute('data-theme', next);
    SN.set('theme', next);
    updateThemeIcon(next);
  });
}

function updateThemeIcon(theme) {
  const icon = document.getElementById('theme-icon');
  if (!icon) return;
  icon.className = theme === 'dark' ? 'fas fa-sun' : 'fas fa-moon';
}

// ----------------------------------------------------------
// Language
// ----------------------------------------------------------
function initLangToggle() {
  const btn = document.getElementById('lang-toggle');
  if (!btn) return;
  btn.addEventListener('click', () => {
    const current = SN.get('language');
    const next    = current === 'en' ? 'ne' : 'en';
    SN.set('language', next);
    applyLanguage(next);
    document.getElementById('lang-text').textContent = next === 'ne' ? 'EN' : 'NE';
  });
}

function applyLanguage(lang) {
  const isNe = lang === 'ne';
  document.body.classList.toggle('lang-ne', isNe);
  document.querySelectorAll('.nav-label-en').forEach(el => {
    el.style.display = isNe ? 'none' : '';
  });
  document.querySelectorAll('.nav-label-ne').forEach(el => {
    el.style.display = isNe ? '' : 'none';
  });
  const langText = document.getElementById('lang-text');
  if (langText) langText.textContent = isNe ? 'EN' : 'NE';
}

// ----------------------------------------------------------
// Mobile Nav
// ----------------------------------------------------------
function initMobileNav() {
  const hamburger = document.getElementById('hamburger');
  const navLinks  = document.getElementById('nav-links');
  if (!hamburger || !navLinks) return;

  hamburger.addEventListener('click', () => {
    const isOpen = navLinks.classList.toggle('open');
    hamburger.setAttribute('aria-expanded', isOpen);
    hamburger.querySelectorAll('span').forEach((s, i) => {
      s.style.transform = isOpen
        ? i === 0 ? 'rotate(45deg) translate(5px,5px)'
        : i === 1 ? 'opacity:0'
        : 'rotate(-45deg) translate(5px,-5px)'
        : '';
    });
  });

  document.addEventListener('click', (e) => {
    if (!hamburger.contains(e.target) && !navLinks.contains(e.target)) {
      navLinks.classList.remove('open');
    }
  });
}

// ----------------------------------------------------------
// Scroll to top
// ----------------------------------------------------------
function initScrollTop() {
  const btn = document.getElementById('scroll-top');
  if (!btn) return;
  window.addEventListener('scroll', () => {
    btn.classList.toggle('visible', window.scrollY > 400);
  });
  btn.addEventListener('click', () => window.scrollTo({ top: 0, behavior: 'smooth' }));
}

// ----------------------------------------------------------
// Notification dropdown
// ----------------------------------------------------------
function initNotifDropdown() {
  const notifBtn = document.getElementById('notif-btn');
  const notifDdp = document.getElementById('notif-dropdown');
  if (!notifBtn || !notifDdp) return;

  notifBtn.addEventListener('click', (e) => {
    e.stopPropagation();
    const isVisible = notifDdp.style.display !== 'none';
    notifDdp.style.display = isVisible ? 'none' : 'block';
    if (!isVisible) renderNotifications();
  });

  document.addEventListener('click', (e) => {
    if (!notifBtn.contains(e.target) && !notifDdp.contains(e.target)) {
      notifDdp.style.display = 'none';
    }
  });

  const markBtn = document.getElementById('mark-read-btn');
  if (markBtn) {
    markBtn.addEventListener('click', () => {
      const notifs = getNotifications();
      notifs.forEach(n => n.read = true);
      localStorage.setItem('sn_notifications', JSON.stringify(notifs));
      renderNotifications();
      updateNotifBadge();
    });
  }
}

function getNotifications() {
  return JSON.parse(localStorage.getItem('sn_notifications') || '[]');
}

function addNotification(title, body, type = 'info') {
  const notifs = getNotifications();
  notifs.unshift({ id: Date.now(), title, body, type, read: false, time: new Date().toISOString() });
  if (notifs.length > 20) notifs.splice(20);
  localStorage.setItem('sn_notifications', JSON.stringify(notifs));
  updateNotifBadge();
  renderNotifications();
}

function updateNotifBadge() {
  const dot  = document.getElementById('notif-dot');
  const unread = getNotifications().filter(n => !n.read).length;
  if (dot) dot.style.display = unread > 0 ? 'block' : 'none';
}

function renderNotifications() {
  const list   = document.getElementById('notif-list');
  const notifs = getNotifications();
  if (!list) return;

  if (notifs.length === 0) {
    list.innerHTML = `<div style="padding:32px;text-align:center;color:var(--text-muted);">
      <i class="fas fa-bell" style="font-size:2rem;margin-bottom:8px;display:block;opacity:0.3"></i>No notifications yet</div>`;
    return;
  }

  const colorMap = { info:'var(--accent-blue)', warning:'var(--accent-yellow)', danger:'var(--accent-red)', success:'var(--accent-green)' };

  list.innerHTML = notifs.map(n => {
    const color = colorMap[n.type] || colorMap.info;
    const time  = new Date(n.time).toLocaleTimeString([], { hour:'2-digit', minute:'2-digit' });
    return `<div class="notif-item ${n.read ? '' : 'unread'}">
      <div class="notif-dot" style="background:${color}"></div>
      <div>
        <div class="notif-title">${escapeHtml(n.title)}</div>
        <div class="notif-body">${escapeHtml(n.body)}</div>
        <div class="notif-time">${time}</div>
      </div>
    </div>`;
  }).join('');

  updateNotifBadge();
}

// ----------------------------------------------------------
// Geolocation — called from splash screen
// ----------------------------------------------------------
window.initApp = function() {
  const btn    = document.getElementById('start-btn');
  const spinner = document.getElementById('splash-spinner');
  const icon   = document.getElementById('start-icon');
  const text   = document.getElementById('start-text');

  btn.disabled = true;
  spinner.classList.add('active');
  if (icon) icon.style.display = 'none';
  if (text) text.textContent   = 'Detecting location…';

  if (!navigator.geolocation) {
    useDefaultLocation();
    return;
  }

  navigator.geolocation.getCurrentPosition(
    (pos) => {
      const lat = pos.coords.latitude;
      const lon = pos.coords.longitude;
      SN.saveLocation(lat, lon);
      hideSplash();
      window.dispatchEvent(new CustomEvent('sn:location', { detail: { lat, lon } }));
    },
    () => {
      // Permission denied — use Kathmandu default
      useDefaultLocation();
    },
    { timeout: 10000, maximumAge: 60000, enableHighAccuracy: true }
  );
};

function useDefaultLocation() {
  // Default: Kathmandu
  const lat = 27.7172, lon = 85.3240;
  SN.saveLocation(lat, lon);
  hideSplash();
  window.dispatchEvent(new CustomEvent('sn:location', { detail: { lat, lon } }));
  addNotification('Location defaulted', 'Using Kathmandu as your location. Enable GPS for accurate data.', 'info');
}

function hideSplash() {
  const splash = document.getElementById('splash-screen');
  const dash   = document.getElementById('dashboard');
  if (splash) splash.classList.add('hidden');
  if (dash)   { dash.style.display = 'block'; }
  setTimeout(() => { if (splash) splash.style.display = 'none'; }, 600);

  // Request notification permission
  if ('Notification' in window && Notification.permission === 'default') {
    Notification.requestPermission();
  }
}

// ----------------------------------------------------------
// Weather code → icon (Meteo WMO codes)
// ----------------------------------------------------------
window.wmoIcon = function(code) {
  if (code === 0)                return '☀️';
  if (code <= 2)                 return '🌤️';
  if (code === 3)                return '☁️';
  if (code <= 49)                return '🌫️';
  if (code <= 59)                return '🌦️';
  if (code <= 69)                return '🌨️';
  if (code <= 79)                return '❄️';
  if (code <= 82)                return '🌧️';
  if (code <= 84)                return '🌩️';
  if (code <= 99)                return '⛈️';
  return '🌡️';
};

// ----------------------------------------------------------
// Alert icon by type
// ----------------------------------------------------------
window.alertTypeIcon = function(type) {
  const map = {
    earthquake: 'fa-circle-radiation', flood: 'fa-water', landslide: 'fa-mountain',
    storm: 'fa-cloud-bolt', lightning: 'fa-bolt', snowfall: 'fa-snowflake',
    heatwave: 'fa-temperature-high', cold_wave: 'fa-temperature-low',
    heavy_rain: 'fa-cloud-rain', other: 'fa-triangle-exclamation'
  };
  return map[type] || map.other;
};

window.alertTypeColor = function(type) {
  const map = {
    earthquake:'icon-orange', flood:'icon-blue', landslide:'icon-red',
    storm:'icon-purple', lightning:'icon-yellow', snowfall:'icon-cyan',
    heatwave:'icon-orange', cold_wave:'icon-blue', heavy_rain:'icon-blue', other:'icon-green'
  };
  return map[type] || map.other;
};

// ----------------------------------------------------------
// Utilities
// ----------------------------------------------------------
window.escapeHtml = function(str) {
  const d = document.createElement('div');
  d.appendChild(document.createTextNode(String(str)));
  return d.innerHTML;
};

window.formatTime = function(ts) {
  return new Date(ts * 1000).toLocaleTimeString([], { hour:'2-digit', minute:'2-digit' });
};

window.formatDate = function(dateStr) {
  return new Date(dateStr).toLocaleDateString('en-US', { weekday:'short', month:'short', day:'numeric' });
};

// Init badge on load
document.addEventListener('DOMContentLoaded', updateNotifBadge);

// Check if already have location (returning visitor) — skip splash
document.addEventListener('DOMContentLoaded', () => {
  const loc = SN.getLocation();
  if (loc.lat && loc.lon && loc.lat !== 0 && loc.lon !== 0) {
    const splash = document.getElementById('splash-screen');
    const dash   = document.getElementById('dashboard');
    if (splash) splash.classList.add('hidden');
    if (dash)   dash.style.display = 'block';
    setTimeout(() => { if (splash) splash.style.display = 'none'; }, 500);
    window.dispatchEvent(new CustomEvent('sn:location', { detail: { lat: loc.lat, lon: loc.lon } }));
  }
});

// Poor Man's Cron for alerts
setTimeout(() => {
  const url = document.querySelector('meta[name="app-url"]')?.content || '';
  if (url) fetch(url + '/api/cron_alerts.php').catch(() => {});
}, 10000);

