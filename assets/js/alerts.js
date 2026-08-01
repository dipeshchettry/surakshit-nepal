// ============================================================
// Surakshit Nepal — Alerts Page JS (alerts.js)
// ============================================================

'use strict';

const APP_URL_ALERTS = document.querySelector('meta[name="app-url"]')?.content || '';
let allAlerts        = [];
let currentFilter    = 'all';

// ----------------------------------------------------------
// Boot
// ----------------------------------------------------------
document.addEventListener('DOMContentLoaded', () => {
  const loc = SN.getLocation();
  const lat = loc.lat || 27.7172;
  const lon = loc.lon || 85.3240;
  loadAlerts(false, lat, lon);
  initFilterTabs();
});

window.addEventListener('sn:location', (e) => {
  loadAlerts(false, e.detail.lat, e.detail.lon);
});

// ----------------------------------------------------------
// Load alerts
// ----------------------------------------------------------
window.loadAlerts = async function(forceRefresh = false, lat, lon) {
  if (!lat || !lon) {
    const loc = SN.getLocation();
    lat = loc.lat || 27.7172;
    lon = loc.lon || 85.3240;
  }

  try {
    const res  = await fetch(`${APP_URL_ALERTS}/api/alerts.php?lat=${lat}&lon=${lon}`);
    const data = await res.json();
    allAlerts  = data.alerts || [];

    updateCount(allAlerts.length);
    renderAlerts(allAlerts, currentFilter);
    renderUSGSFeed(allAlerts);

    // Notify badge
    if (allAlerts.some(a => a.severity === 'red')) {
      addNotification('🔴 Red Alert', 'Extreme disaster event detected near Nepal.', 'danger');
    } else if (allAlerts.some(a => a.severity === 'orange')) {
      addNotification('🟠 Orange Alert', 'Severe disaster warning issued for Nepal region.', 'warning');
    }

  } catch (err) {
    const grid = document.getElementById('alerts-grid');
    if (grid) grid.innerHTML = `<div class="glass-card" style="padding:32px;text-align:center;color:var(--text-muted);grid-column:1/-1;">
      <i class="fas fa-wifi" style="font-size:3rem;display:block;margin-bottom:12px;opacity:0.3;"></i>
      <p>Could not load alerts. Check your connection.</p>
      <button onclick="loadAlerts(true)" style="margin-top:12px;padding:8px 20px;border-radius:10px;border:1px solid var(--glass-border);background:var(--bg-card);color:var(--accent-blue);cursor:pointer;">Retry</button>
    </div>`;
  }
};

// ----------------------------------------------------------
// Render alert cards
// ----------------------------------------------------------
function renderAlerts(alerts, filter) {
  const grid = document.getElementById('alerts-grid');
  const noEl = document.getElementById('no-alerts');
  if (!grid) return;

  const filtered = filter === 'all' ? alerts : alerts.filter(a => {
    if (filter === 'other') return !['earthquake','flood','landslide','storm'].includes(a.type);
    return a.type === filter;
  });

  if (!filtered.length) {
    grid.innerHTML = '';
    grid.style.display = 'none';
    if (noEl) noEl.style.display = 'block';
    return;
  }
  if (noEl) noEl.style.display = 'none';
  grid.style.display = 'grid';

  grid.innerHTML = filtered.map(a => renderAlertCard(a)).join('');
}

function renderAlertCard(a) {
  const magLine = a.magnitude ? `<span><i class="fas fa-circle-radiation"></i> M${a.magnitude} (${a.depth_km}km deep)</span>` : '';
  return `
    <div class="glass-card alert-card sev-${a.severity}" data-type="${a.type}" data-severity="${a.severity}">
      <div class="alert-header">
        <div class="alert-icon ${alertTypeColor(a.type)}">
          <i class="fas ${alertTypeIcon(a.type)}"></i>
        </div>
        <div style="flex:1;min-width:0;">
          <div class="alert-title">${escapeHtml(a.title)}</div>
          <div class="alert-meta">
            <span class="risk-badge risk-${a.severity}" style="font-size:0.65rem;padding:2px 8px;margin-bottom:4px;display:inline-flex;">
              ${a.severity.toUpperCase()}
            </span>
            ${magLine}
          </div>
        </div>
      </div>
      <div class="alert-meta">
        <i class="fas fa-location-dot"></i> ${escapeHtml(a.affected_area || 'Nepal Region')} &nbsp;·&nbsp;
        <i class="fas fa-clock"></i> ${new Date(a.time).toLocaleString()} &nbsp;·&nbsp;
        <i class="fas fa-database"></i> ${escapeHtml(a.source)}
      </div>
      ${a.safety_tips ? `<div class="alert-tips">
        <i class="fas fa-shield-check" style="color:var(--accent-green);margin-right:6px;"></i>
        ${escapeHtml(a.safety_tips)}
      </div>` : ''}
      ${a.source_url ? `<a href="${escapeHtml(a.source_url)}" target="_blank" rel="noopener"
        style="display:inline-flex;align-items:center;gap:6px;margin-top:12px;font-size:0.78rem;color:var(--accent-blue);text-decoration:none;">
        <i class="fas fa-external-link"></i> View source
      </a>` : ''}
    </div>`;
}

// ----------------------------------------------------------
// USGS Feed Table
// ----------------------------------------------------------
function renderUSGSFeed(alerts) {
  const el = document.getElementById('usgs-feed');
  if (!el) return;

  const eqs = alerts.filter(a => a.type === 'earthquake').slice(0, 10);
  if (!eqs.length) {
    el.innerHTML = '<p class="text-muted" style="padding:16px;">No recent earthquakes detected in Nepal region.</p>';
    return;
  }

  el.innerHTML = `
    <div style="overflow-x:auto;">
      <table style="width:100%;border-collapse:collapse;font-size:0.85rem;">
        <thead>
          <tr style="border-bottom:1px solid var(--glass-border);">
            <th style="padding:10px;text-align:left;color:var(--text-muted);font-weight:600;">Magnitude</th>
            <th style="padding:10px;text-align:left;color:var(--text-muted);font-weight:600;">Location</th>
            <th style="padding:10px;text-align:left;color:var(--text-muted);font-weight:600;">Depth</th>
            <th style="padding:10px;text-align:left;color:var(--text-muted);font-weight:600;">Time</th>
            <th style="padding:10px;text-align:left;color:var(--text-muted);font-weight:600;">Severity</th>
          </tr>
        </thead>
        <tbody>
          ${eqs.map(eq => `
            <tr style="border-bottom:1px solid var(--glass-border);transition:background 0.2s;" onmouseover="this.style.background='rgba(255,255,255,0.03)'" onmouseout="this.style.background=''">
              <td style="padding:12px;">
                <span style="font-weight:800;font-size:1rem;color:${magColor(eq.magnitude)}">${eq.magnitude}</span>
              </td>
              <td style="padding:12px;">${escapeHtml(eq.affected_area || 'Unknown')}</td>
              <td style="padding:12px;">${eq.depth_km ?? '?'} km</td>
              <td style="padding:12px;">${new Date(eq.time).toLocaleString()}</td>
              <td style="padding:12px;">
                <span class="risk-badge risk-${eq.severity}" style="font-size:0.65rem;padding:2px 8px;display:inline-flex;">
                  ${eq.severity.toUpperCase()}
                </span>
              </td>
            </tr>`).join('')}
        </tbody>
      </table>
    </div>`;
}

function magColor(mag) {
  if (mag >= 6) return 'var(--accent-red)';
  if (mag >= 5) return 'var(--accent-orange)';
  if (mag >= 3) return 'var(--accent-yellow)';
  return 'var(--accent-green)';
}

// ----------------------------------------------------------
// Filter tabs
// ----------------------------------------------------------
function initFilterTabs() {
  const tabsEl = document.getElementById('alert-filters');
  if (!tabsEl) return;

  tabsEl.addEventListener('click', (e) => {
    const btn = e.target.closest('[data-filter]');
    if (!btn) return;
    tabsEl.querySelectorAll('[data-filter]').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    currentFilter = btn.dataset.filter;
    renderAlerts(allAlerts, currentFilter);
  });
}

function updateCount(count) {
  const el = document.getElementById('alert-count');
  if (el) el.textContent = `${count} alert${count !== 1 ? 's' : ''} found`;
}
