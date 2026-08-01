<?php
$pageTitle = 'Emergency';
$pageDesc  = 'Emergency contacts, safety tips, emergency kit checklist, and nearby services for Nepal.';

// Fetch contacts from DB
require_once __DIR__ . '/api/config/config.php';
require_once __DIR__ . '/api/config/database.php';
require_once __DIR__ . '/functions/helpers.php';

$contacts = [];
try {
    $db = Database::getInstance();
    $stmt = $db->prepare('SELECT * FROM emergency_contacts WHERE is_active = 1 ORDER BY FIELD(category,"police","ambulance","fire","disaster","hospital","search_rescue","electricity","other")');
    $stmt->execute();
    $contacts = $stmt->fetchAll();
} catch (Exception $e) {
    // DB not ready — use hardcoded fallback
    $contacts = [
        ['category'=>'police',   'name'=>'Nepal Police Emergency', 'phone'=>'100', 'district'=>'National'],
        ['category'=>'ambulance','name'=>'Ambulance Service',       'phone'=>'102', 'district'=>'National'],
        ['category'=>'fire',     'name'=>'Fire Brigade',            'phone'=>'101', 'district'=>'National'],
        ['category'=>'disaster', 'name'=>'NDRRMA',                  'phone'=>'1149','district'=>'National'],
    ];
}

$catConfig = [
    'police'       => ['icon'=>'fa-shield','color'=>'icon-blue',  'label'=>'Police'],
    'ambulance'    => ['icon'=>'fa-truck-medical','color'=>'icon-red','label'=>'Ambulance'],
    'fire'         => ['icon'=>'fa-fire','color'=>'icon-orange','label'=>'Fire'],
    'disaster'     => ['icon'=>'fa-circle-radiation','color'=>'icon-orange','label'=>'Disaster'],
    'hospital'     => ['icon'=>'fa-hospital','color'=>'icon-green','label'=>'Hospital'],
    'search_rescue'=> ['icon'=>'fa-people-carry-box','color'=>'icon-cyan','label'=>'Search & Rescue'],
    'electricity'  => ['icon'=>'fa-bolt','color'=>'icon-yellow','label'=>'Electricity'],
    'other'        => ['icon'=>'fa-phone','color'=>'icon-purple','label'=>'Other'],
];

include __DIR__ . '/includes/header.php';
?>

<div class="container-main page-fade">
  <div class="page-hero">
    <h1><i class="fas fa-phone-volume" style="color:var(--accent-red)"></i> Emergency</h1>
    <p>Critical contacts, safety guidelines & emergency kit checklist</p>
  </div>

  <!-- SOS Banner -->
  <div class="glass-card mb-24" style="padding:24px;background:rgba(239,68,68,0.08);border:1px solid rgba(239,68,68,0.25);">
    <div style="display:flex;align-items:center;gap:20px;flex-wrap:wrap;">
      <div style="font-size:3rem;animation:risk-pulse 2s infinite;">🆘</div>
      <div>
        <h2 style="font-size:1.3rem;font-weight:900;color:var(--accent-red);margin-bottom:6px;">In an Emergency?</h2>
        <p style="color:var(--text-secondary);font-size:0.9rem;">Call these numbers immediately. Stay calm, give your location, describe the situation.</p>
      </div>
      <div style="margin-left:auto;display:flex;gap:12px;flex-wrap:wrap;">
        <a href="tel:100" style="padding:12px 24px;border-radius:50px;background:var(--gradient-accent);color:white;font-weight:800;font-size:1.1rem;text-decoration:none;display:flex;align-items:center;gap:8px;">
          <i class="fas fa-phone"></i> 100
        </a>
        <a href="tel:1149" style="padding:12px 24px;border-radius:50px;background:var(--gradient-danger);color:white;font-weight:800;font-size:1.1rem;text-decoration:none;display:flex;align-items:center;gap:8px;">
          <i class="fas fa-siren-on"></i> 1149
        </a>
      </div>
    </div>
  </div>

  <!-- Emergency Contacts Grid -->
  <h2 class="section-title mb-16"><i class="fas fa-address-book"></i> Emergency Contacts</h2>
  <div class="emergency-grid mb-24">
    <?php foreach ($contacts as $c):
      $cfg = $catConfig[$c['category']] ?? $catConfig['other'];
    ?>
    <a href="tel:<?= e($c['phone']) ?>" class="glass-card contact-card" title="Call <?= e($c['name']) ?>">
      <div class="contact-icon <?= $cfg['color'] ?>">
        <i class="fas <?= $cfg['icon'] ?>"></i>
      </div>
      <div class="contact-info">
        <h3><?= e($c['name']) ?></h3>
        <div class="contact-phone"><i class="fas fa-phone-flip" style="font-size:0.9rem"></i> <?= e($c['phone']) ?></div>
        <?php if ($c['district']): ?>
        <div class="contact-district"><i class="fas fa-location-dot"></i> <?= e($c['district']) ?></div>
        <?php endif; ?>
      </div>
      <i class="fas fa-phone" style="margin-left:auto;color:var(--accent-green);font-size:1.1rem;"></i>
    </a>
    <?php endforeach; ?>
  </div>

  <!-- Safety Tips + Emergency Kit -->
  <div class="dashboard-grid mb-24">
    <!-- Safety Tips -->
    <div class="col-6">
      <div class="glass-card" style="padding:24px;height:100%;">
        <h2 class="section-title mb-16"><i class="fas fa-shield-check"></i> Safety Tips</h2>
        <div style="display:flex;flex-direction:column;gap:16px;">
          <?php
          $tips = [
            ['icon'=>'fa-circle-radiation','color'=>'icon-orange','title'=>'Earthquake',
             'tips'=>['Drop, Cover, Hold On','Move away from windows & shelves','Do NOT run outside during shaking','After shaking: check for gas leaks','Expect aftershocks']],
            ['icon'=>'fa-water','color'=>'icon-blue','title'=>'Flood',
             'tips'=>['Move to higher ground immediately','Do NOT walk through flood water','Avoid bridges over fast-moving water','Keep emergency kit elevated','Follow evacuation orders']],
            ['icon'=>'fa-mountain','color'=>'icon-red','title'=>'Landslide',
             'tips'=>['Listen for unusual sounds','Move away from slope if rainfall is heavy','Do not shelter near cliff bases','Watch for sudden changes in water clarity','Alert neighbours immediately']],
          ];
          foreach ($tips as $tip): ?>
          <div style="border-radius:14px;padding:16px;background:rgba(255,255,255,0.04);border:1px solid var(--glass-border);">
            <div class="d-flex align-center gap-10 mb-8">
              <div class="stat-icon <?= $tip['color'] ?>" style="width:36px;height:36px;">
                <i class="fas <?= $tip['icon'] ?>" style="font-size:0.9rem;"></i>
              </div>
              <strong><?= $tip['title'] ?></strong>
            </div>
            <ul style="list-style:none;display:flex;flex-direction:column;gap:6px;">
              <?php foreach ($tip['tips'] as $t): ?>
              <li style="font-size:0.83rem;color:var(--text-secondary);display:flex;align-items:flex-start;gap:8px;">
                <i class="fas fa-check-circle" style="color:var(--accent-green);margin-top:2px;flex-shrink:0;"></i>
                <?= e($t) ?>
              </li>
              <?php endforeach; ?>
            </ul>
          </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>

    <!-- Emergency Kit Checklist -->
    <div class="col-6">
      <div class="glass-card" style="padding:24px;height:100%;">
        <h2 class="section-title mb-16"><i class="fas fa-kit-medical"></i> Emergency Kit Checklist</h2>
        <p style="font-size:0.82rem;color:var(--text-secondary);margin-bottom:16px;">
          Keep this kit ready for 72+ hours. Check items as you prepare.
        </p>
        <ul class="checklist" id="kit-checklist">
          <?php
          $items = [
            'Water (4 litres per person per day)',
            'Non-perishable food (3-day supply)',
            'Battery-powered or hand-crank radio',
            'Flashlight & extra batteries',
            'First aid kit',
            'Whistle to signal for help',
            'Dust masks / N95 respirators',
            'Plastic sheeting & duct tape',
            'Moist towelettes, garbage bags, plastic ties',
            'Wrench or pliers to turn off utilities',
            'Manual can opener',
            'Local maps & important documents (ID, insurance)',
            'Cell phone with chargers & backup battery',
            'Prescription medications (7-day supply)',
            'Cash in small bills',
            'Warm blankets or sleeping bags',
            'Change of clothes & sturdy shoes',
            'Baby formula & diapers (if applicable)',
          ];
          foreach ($items as $i => $item): ?>
          <li id="kit-<?= $i ?>">
            <input type="checkbox" id="check-<?= $i ?>" onchange="toggleKitItem(<?= $i ?>)">
            <label for="check-<?= $i ?>"><?= e($item) ?></label>
          </li>
          <?php endforeach; ?>
        </ul>
        <div style="margin-top:16px;padding:12px 16px;border-radius:12px;background:rgba(16,185,129,0.1);border:1px solid rgba(16,185,129,0.2);">
          <div style="font-size:0.82rem;color:var(--accent-green);">
            <i class="fas fa-circle-check"></i>
            <span id="kit-progress">0 / <?= count($items) ?> items ready</span>
          </div>
          <div style="height:4px;background:var(--glass-border);border-radius:2px;margin-top:8px;overflow:hidden;">
            <div id="kit-bar" style="height:100%;background:var(--gradient-success);border-radius:2px;width:0%;transition:width 0.4s ease;"></div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Nearby Services Map Preview -->
  <div class="glass-card" style="padding:20px;">
    <div class="section-header mb-16">
      <h2 class="section-title"><i class="fas fa-map-location-dot"></i> Nearby Emergency Services</h2>
      <a href="<?= APP_URL ?>/map.php" class="section-link">Open Full Map <i class="fas fa-arrow-right"></i></a>
    </div>
    <div style="height:300px;border-radius:14px;overflow:hidden;background:rgba(0,0,0,0.2);display:grid;place-items:center;color:var(--text-muted);">
      <div style="text-align:center;">
        <i class="fas fa-map-location-dot" style="font-size:3rem;display:block;margin-bottom:12px;opacity:0.3;"></i>
        <a href="<?= APP_URL ?>/map.php" style="color:var(--accent-blue);text-decoration:none;font-weight:600;">
          View Interactive Map →
        </a>
      </div>
    </div>
  </div>
</div>

<script>
function toggleKitItem(idx) {
  const li = document.getElementById('kit-' + idx);
  const cb = document.getElementById('check-' + idx);
  const items = <?= count($items) ?>;
  li.classList.toggle('checked', cb.checked);

  // Save to localStorage
  const saved = JSON.parse(localStorage.getItem('sn_kit') || '{}');
  saved[idx] = cb.checked;
  localStorage.setItem('sn_kit', JSON.stringify(saved));

  // Update progress
  const done = Object.values(saved).filter(Boolean).length;
  document.getElementById('kit-progress').textContent = done + ' / ' + items + ' items ready';
  document.getElementById('kit-bar').style.width = (done / items * 100) + '%';
}

// Restore checklist state from localStorage
(function() {
  const saved = JSON.parse(localStorage.getItem('sn_kit') || '{}');
  const items = <?= count($items) ?>;
  let done = 0;
  Object.entries(saved).forEach(([idx, checked]) => {
    if (checked) {
      const cb = document.getElementById('check-' + idx);
      const li = document.getElementById('kit-' + idx);
      if (cb) { cb.checked = true; li.classList.add('checked'); done++; }
    }
  });
  document.getElementById('kit-progress').textContent = done + ' / ' + items + ' items ready';
  document.getElementById('kit-bar').style.width = (done / items * 100) + '%';
})();
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
