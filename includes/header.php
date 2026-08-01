<?php
// ============================================================
// Surakshit Nepal — Header Include
// ============================================================
require_once __DIR__ . '/../api/config/config.php';
require_once __DIR__ . '/../functions/helpers.php';

if (session_status() === PHP_SESSION_NONE) session_start();

$currentPage = basename($_SERVER['PHP_SELF'], '.php');
$pageTitle   = $pageTitle ?? 'Surakshit Nepal';
$pageDesc    = $pageDesc  ?? 'AI-powered Weather & Disaster Early Warning for Nepal';
?>
<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="description" content="<?= e($pageDesc) ?>">
  <meta name="keywords" content="Nepal weather, disaster alert, earthquake, flood, landslide, early warning, Surakshit Nepal">
  <meta name="author" content="Surakshit Nepal">
  <meta name="theme-color" content="#3b82f6">

  <!-- Open Graph -->
  <meta property="og:title" content="<?= e($pageTitle) ?>">
  <meta property="og:description" content="<?= e($pageDesc) ?>">
  <meta property="og:type" content="website">
  <meta property="og:url" content="<?= e(APP_URL) ?>">

  <!-- PWA -->
  <link rel="manifest" href="<?= APP_URL ?>/manifest.json">
  <link rel="apple-touch-icon" href="<?= APP_URL ?>/assets/images/icon-192.png">

  <title><?= e($pageTitle) ?> — Surakshit Nepal</title>

  <!-- Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&family=Noto+Sans+Devanagari:wght@400;500;600;700&display=swap" rel="stylesheet">

  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

  <!-- Main CSS -->
  <link rel="stylesheet" href="<?= APP_URL ?>/assets/css/style.css">

  <!-- CSRF meta (for JS requests) -->
  <meta name="csrf-token" content="<?= csrf_token() ?>">
  <meta name="app-url" content="<?= APP_URL ?>">
  <meta name="onesignal-app-id" content="<?= e(ONESIGNAL_APP_ID) ?>">

  <!-- OneSignal SDK -->
  <script src="https://cdn.onesignal.com/sdks/web/v16/OneSignalSDK.page.js" defer></script>
</head>
<body class="">

<!-- Animated Background -->
<div class="app-bg"></div>

<?php include __DIR__ . '/nav.php'; ?>

<div class="page-wrapper">
