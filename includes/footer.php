<?php
// ============================================================
// Surakshit Nepal — Footer Include
// ============================================================
?>
</div><!-- .page-wrapper -->

<!-- Scroll to top -->
<button id="scroll-top" aria-label="Scroll to top">
  <i class="fas fa-chevron-up"></i>
</button>

<!-- Footer -->
<footer class="footer">
  <div class="footer-logo">
    <i class="fas fa-shield-halved" style="color:var(--accent-blue)"></i>
    Surakshit Nepal
    <span style="font-family:var(--font-ne);color:var(--accent-cyan);font-size:0.85rem;">— सुरक्षित नेपाल</span>
  </div>
  <p style="margin-bottom:8px;">
    AI-powered Weather & Disaster Early Warning Platform for Nepal
  </p>
  <p>
    Data sources:
    <a href="https://openweathermap.org" target="_blank" rel="noopener">OpenWeatherMap</a> ·
    <a href="https://open-meteo.com" target="_blank" rel="noopener">Open-Meteo</a> ·
    <a href="https://earthquake.usgs.gov" target="_blank" rel="noopener">USGS</a> ·
    <a href="https://www.gdacs.org" target="_blank" rel="noopener">GDACS</a> ·
    <a href="https://reliefweb.int" target="_blank" rel="noopener">ReliefWeb</a>
  </p>
  <p style="margin-top:8px;">
    Emergency: <strong style="color:var(--accent-red)">1149 (NDRRMA)</strong> ·
    Police: <strong style="color:var(--accent-blue)">100</strong> ·
    Ambulance: <strong style="color:var(--accent-green)">102</strong>
  </p>
  <p style="margin-top:12px;font-size:0.72rem;">
    &copy; <?= date('Y') ?> Surakshit Nepal. Built for the safety of Nepal. |
    <a href="<?= APP_URL ?>/settings.php">Settings</a>
  </p>
</footer>

<!-- Core JS -->
<script src="<?= APP_URL ?>/assets/js/app.js?v=<?= filemtime(__DIR__ . '/../assets/js/app.js') ?>" defer></script>

<!-- OneSignal SDK -->
<script src="https://cdn.onesignal.com/sdks/web/v16/OneSignalSDK.page.js" defer></script>
<script src="<?= APP_URL ?>/assets/js/notifications.js" defer></script>

<?php if (isset($extraScripts)): ?>
  <?php foreach ($extraScripts as $script): ?>
    <script src="<?= APP_URL ?>/<?= e($script) ?>?v=<?= filemtime(__DIR__ . '/../' . $script) ?>" defer></script>
  <?php endforeach; ?>
<?php endif; ?>

</body>
</html>
