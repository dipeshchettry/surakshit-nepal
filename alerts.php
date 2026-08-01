<?php
$pageTitle   = 'Disaster Alerts';
$pageDesc    = 'Live disaster alerts for Nepal — earthquakes, floods, landslides, storms, and more.';
$extraScripts = ['assets/js/alerts.js'];
include __DIR__ . '/includes/header.php';
?>

<div class="container-main page-fade">
  <div class="page-hero">
    <h1><i class="fas fa-triangle-exclamation" style="color:var(--accent-orange)"></i> Disaster Alerts</h1>
    <p>Real-time disaster monitoring for Nepal — updated every 30 minutes</p>
  </div>

  <!-- Filter & sort bar -->
  <div class="glass-card mb-20" style="padding:16px 20px;">
    <div style="display:flex;align-items:center;gap:16px;flex-wrap:wrap;">
      <div class="filter-tabs" id="alert-filters">
        <button class="filter-tab active" data-filter="all">All</button>
        <button class="filter-tab" data-filter="earthquake"><i class="fas fa-circle-radiation"></i> Earthquake</button>
        <button class="filter-tab" data-filter="flood"><i class="fas fa-water"></i> Flood</button>
        <button class="filter-tab" data-filter="landslide"><i class="fas fa-mountain"></i> Landslide</button>
        <button class="filter-tab" data-filter="storm"><i class="fas fa-cloud-bolt"></i> Storm</button>
        <button class="filter-tab" data-filter="other"><i class="fas fa-circle-info"></i> Other</button>
      </div>
      <div style="margin-left:auto;display:flex;gap:8px;align-items:center;">
        <span class="text-muted text-small" id="alert-count">Loading…</span>
        <button onclick="loadAlerts(true)" style="padding:8px 14px;border:1px solid var(--glass-border);border-radius:10px;background:var(--bg-card);color:var(--text-secondary);cursor:pointer;font-size:0.8rem;">
          <i class="fas fa-rotate-right"></i> Refresh
        </button>
      </div>
    </div>
  </div>

  <!-- Severity Legend -->
  <div class="d-flex gap-12 mb-20" style="flex-wrap:wrap;">
    <div class="d-flex align-center gap-8" style="font-size:0.82rem;">
      <span style="width:12px;height:12px;border-radius:3px;background:var(--accent-green);display:inline-block;"></span> Green — Minor
    </div>
    <div class="d-flex align-center gap-8" style="font-size:0.82rem;">
      <span style="width:12px;height:12px;border-radius:3px;background:var(--accent-yellow);display:inline-block;"></span> Yellow — Moderate
    </div>
    <div class="d-flex align-center gap-8" style="font-size:0.82rem;">
      <span style="width:12px;height:12px;border-radius:3px;background:var(--accent-orange);display:inline-block;"></span> Orange — Severe
    </div>
    <div class="d-flex align-center gap-8" style="font-size:0.82rem;">
      <span style="width:12px;height:12px;border-radius:3px;background:var(--accent-red);display:inline-block;"></span> Red — Extreme
    </div>
  </div>

  <!-- Alerts Grid -->
  <div id="alerts-grid" style="display:grid;grid-template-columns:repeat(auto-fill,minmax(340px,1fr));gap:16px;">
    <!-- Skeleton -->
    <?php for ($i = 0; $i < 6; $i++): ?>
    <div class="glass-card" style="padding:20px;">
      <div class="skel-text skeleton" style="width:60%;height:18px;"></div>
      <div class="skel-text skeleton" style="width:40%;height:12px;margin-top:8px;"></div>
      <div class="skel-big skeleton" style="height:60px;"></div>
    </div>
    <?php endfor; ?>
  </div>

  <!-- Empty state -->
  <div id="no-alerts" style="display:none;text-align:center;padding:60px 20px;color:var(--text-muted);">
    <i class="fas fa-shield-check" style="font-size:4rem;display:block;margin-bottom:16px;color:var(--accent-green);opacity:0.7;"></i>
    <h3 style="color:var(--text-primary);margin-bottom:8px;">No Active Alerts</h3>
    <p>No disaster alerts for this filter. Stay prepared!</p>
  </div>

  <!-- USGS Live Earthquake Feed -->
  <div class="glass-card mt-24" style="padding:24px;">
    <div class="section-header mb-16">
      <h2 class="section-title">
        <i class="fas fa-satellite-dish" style="color:var(--accent-red)"></i>
        USGS Live Earthquake Feed
      </h2>
      <a href="https://earthquake.usgs.gov/earthquakes/map/" target="_blank" rel="noopener" class="section-link">
        USGS Map <i class="fas fa-external-link"></i>
      </a>
    </div>
    <div id="usgs-feed">
      <div class="text-muted" style="padding:16px;text-align:center;">Loading USGS data…</div>
    </div>
  </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
