<?php
// ============================================================
// Surakshit Nepal — Navigation Include
// ============================================================
$currentPage = basename($_SERVER['PHP_SELF'], '.php');
$navItems = [
  'index'     => ['icon' => 'fa-house',          'label' => 'Dashboard',       'label_ne' => 'ड्यासबोर्ड'],
  'weather'   => ['icon' => 'fa-cloud-sun',       'label' => 'Weather',         'label_ne' => 'मौसम'],
  'map'       => ['icon' => 'fa-map-location-dot','label' => 'Map',             'label_ne' => 'नक्सा'],
  'alerts'    => ['icon' => 'fa-triangle-exclamation','label' => 'Alerts',      'label_ne' => 'सूचना'],
  'emergency' => ['icon' => 'fa-phone-volume',    'label' => 'Emergency',       'label_ne' => 'आपतकाल'],
];
?>

<nav class="navbar-custom" role="navigation" aria-label="Main navigation">
  <!-- Brand -->
  <a href="<?= APP_URL ?>/index.php" class="nav-brand">
    <img src="<?= APP_URL ?>/assets/images/logo.png" alt="Logo" style="width: 36px; height: 36px; object-fit: contain;">
    <div>
      <span>Surakshit Nepal</span>
      <span class="brand-ne">सुरक्षित नेपाल</span>
    </div>
  </a>

  <!-- Desktop Nav Links -->
  <ul class="nav-links" id="nav-links" role="list">
    <?php foreach ($navItems as $page => $item): ?>
      <li>
        <a href="<?= APP_URL ?>/<?= $page ?>.php"
           class="<?= $currentPage === $page ? 'active' : '' ?>"
           aria-current="<?= $currentPage === $page ? 'page' : 'false' ?>">
          <i class="fas <?= $item['icon'] ?>" aria-hidden="true"></i>
          <span class="nav-label-en"><?= $item['label'] ?></span>
          <span class="nav-label-ne" style="display:none"><?= $item['label_ne'] ?></span>
        </a>
      </li>
    <?php endforeach; ?>
    <li>
      <a href="<?= APP_URL ?>/settings.php"
         class="<?= $currentPage === 'settings' ? 'active' : '' ?>"
         aria-label="Settings">
        <i class="fas fa-gear" aria-hidden="true"></i>
        <span class="nav-label-en">Settings</span>
        <span class="nav-label-ne" style="display:none">सेटिङ</span>
      </a>
    </li>
  </ul>

  <!-- Actions -->
  <div class="nav-actions">
    <!-- Alert badge -->
    <button class="btn-icon alert-badge" id="notif-btn"
            aria-label="Notifications" title="Notifications">
      <i class="fas fa-bell" aria-hidden="true"></i>
      <span class="badge-dot" id="notif-dot" style="display:none"></span>
    </button>

    <!-- Theme toggle -->
    <button class="btn-icon" id="theme-toggle"
            aria-label="Toggle theme" title="Toggle dark/light mode">
      <i class="fas fa-moon" id="theme-icon" aria-hidden="true"></i>
    </button>

    <!-- Language toggle -->
    <button class="btn-icon" id="lang-toggle"
            aria-label="Toggle language" title="Switch to Nepali">
      <span style="font-size:0.75rem;font-weight:700;" id="lang-text">NE</span>
    </button>
  </div>

  <!-- Mobile hamburger -->
  <button class="nav-hamburger" id="hamburger"
          aria-label="Toggle mobile menu" aria-expanded="false">
    <span></span>
    <span></span>
    <span></span>
  </button>
</nav>

<!-- Notification Dropdown -->
<div id="notif-dropdown" style="display:none;position:fixed;top:var(--nav-height);right:12px;width:340px;z-index:999;"
     class="glass-card">
  <div style="padding:16px 20px;border-bottom:1px solid var(--glass-border);">
    <div class="d-flex align-center gap-8">
      <strong style="font-size:0.95rem;">Notifications</strong>
      <button id="mark-read-btn" style="margin-left:auto;font-size:0.75rem;color:var(--accent-blue);background:none;border:none;cursor:pointer;">
        Mark all read
      </button>
    </div>
  </div>
  <div id="notif-list" style="max-height:360px;overflow-y:auto;">
    <div style="padding:32px;text-align:center;color:var(--text-muted);">
      <i class="fas fa-bell" style="font-size:2rem;margin-bottom:8px;display:block;opacity:0.3"></i>
      No notifications yet
    </div>
  </div>
</div>
