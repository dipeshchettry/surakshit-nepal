<?php
$pageTitle   = 'Weather Details';
$pageDesc    = 'Detailed weather forecast, hourly data, and extended 7-day outlook for your location in Nepal.';
$extraScripts = ['assets/js/weather.js'];
include __DIR__ . '/includes/header.php';
?>

<div class="container-main page-fade">
  <div class="page-hero">
    <h1><i class="fas fa-cloud-sun-rain" style="color:var(--accent-blue)"></i> Weather Details</h1>
    <p>Extended forecast, hourly breakdown & atmospheric conditions</p>
  </div>

  <!-- Current Conditions -->
  <div class="dashboard-grid mb-24">
    <div class="col-6">
      <div class="glass-card hero-card" id="detail-hero">
        <div class="location-info">
          <i class="fas fa-map-pin"></i>
          <span id="d-location">Loading…</span>
          <span id="d-time" style="margin-left:auto;font-size:0.78rem;color:var(--text-muted)"></span>
        </div>
        <div class="temp-display">
          <div>
            <div class="temp-value"><span id="d-temp">—</span>°C</div>
            <div class="weather-desc" id="d-desc">—</div>
            <div class="feels-like" id="d-feels">—</div>
          </div>
          <img id="d-icon" class="weather-icon-large" src="" alt="">
        </div>
      </div>
    </div>

    <div class="col-6" style="display:grid;grid-template-columns:repeat(3,1fr);gap:12px;align-content:start;">
      <?php
      $statItems = [
        ['id'=>'d-humidity','label'=>'Humidity','icon'=>'fa-droplet','class'=>'icon-blue'],
        ['id'=>'d-wind','label'=>'Wind','icon'=>'fa-wind','class'=>'icon-cyan'],
        ['id'=>'d-pressure','label'=>'Pressure','icon'=>'fa-gauge-high','class'=>'icon-purple'],
        ['id'=>'d-visibility','label'=>'Visibility','icon'=>'fa-eye','class'=>'icon-green'],
        ['id'=>'d-rain','label'=>'Rain 1h','icon'=>'fa-cloud-rain','class'=>'icon-blue'],
        ['id'=>'d-clouds','label'=>'Clouds','icon'=>'fa-cloud','class'=>'icon-orange'],
      ];
      foreach ($statItems as $s): ?>
      <div class="glass-card stat-card glass-card-sm" style="padding:16px;">
        <div class="stat-icon <?= $s['class'] ?>"><i class="fas <?= $s['icon'] ?>"></i></div>
        <div class="stat-val" id="<?= $s['id'] ?>">—</div>
        <div class="stat-label"><?= $s['label'] ?></div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>

  <!-- Sunrise / Sunset Visual -->
  <div class="glass-card mb-24" style="padding:24px;">
    <h2 class="section-title mb-16"><i class="fas fa-sun"></i> Sun Position</h2>
    <div style="position:relative;height:80px;background:linear-gradient(90deg,#1e3a5f,#f59e0b,#ef4444,#1e3a5f);border-radius:40px;overflow:hidden;margin-bottom:12px;">
      <div id="sun-indicator" style="position:absolute;top:50%;transform:translate(-50%,-50%);font-size:1.5rem;transition:left 0.5s ease;">☀️</div>
    </div>
    <div style="display:flex;justify-content:space-between;font-size:0.875rem;">
      <span>🌅 Sunrise: <strong id="d-sunrise">—</strong></span>
      <span>🌇 Sunset: <strong id="d-sunset">—</strong></span>
    </div>
  </div>

  <!-- Hourly Forecast -->
  <div class="glass-card mb-24" style="padding:20px 20px 8px;">
    <div class="section-header mb-16">
      <h2 class="section-title"><i class="fas fa-clock"></i> 24-Hour Forecast</h2>
    </div>
    <div class="hourly-scroll">
      <div class="hourly-container" id="d-hourly-container">
        <div class="text-muted" style="padding:16px;">Loading…</div>
      </div>
    </div>
  </div>

  <!-- 7-Day Forecast -->
  <div class="glass-card mb-24" style="overflow:hidden;">
    <div style="padding:20px 20px 12px;">
      <h2 class="section-title"><i class="fas fa-calendar-week"></i> 7-Day Forecast</h2>
    </div>
    <div class="daily-list" id="d-daily-list">
      <div style="padding:24px;text-align:center;color:var(--text-muted);">Loading…</div>
    </div>
  </div>

  <!-- AQI Detail -->
  <div class="glass-card" style="padding:24px;">
    <h2 class="section-title mb-16"><i class="fas fa-lungs"></i> Air Quality Index</h2>
    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(140px,1fr));gap:12px;" id="aqi-detail">
      <div class="text-muted">Loading…</div>
    </div>
  </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
