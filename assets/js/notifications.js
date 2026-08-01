// ============================================================
// Surakshit Nepal — Push Notifications (OneSignal + Web Push)
// ============================================================

'use strict';

document.addEventListener('DOMContentLoaded', () => {
  initNotifications();
});

async function initNotifications() {
  // OneSignal
  window.OneSignalDeferred = window.OneSignalDeferred || [];
  OneSignalDeferred.push(async function(OneSignal) {
    await OneSignal.init({
      appId: getOneSignalAppId(),
      notifyButton: { enable: false },
      allowLocalhostAsSecureOrigin: true,
    });
    
    const enabled = SN.get('notifications', false);
    if (enabled) {
      OneSignal.Notifications.requestPermission();
    }
  });

  // Fallback: native Web Push via Service Worker
  if ('serviceWorker' in navigator && 'PushManager' in window) {
    try {
      const reg = await navigator.serviceWorker.register(
        (document.querySelector('meta[name="app-url"]')?.content || '') + '/assets/js/sw.js'
      );
      window._swReg = reg;
    } catch (e) {
      console.warn('Service Worker registration failed:', e);
    }
  }
}

function getOneSignalAppId() {
  // The app ID is baked in on the server — read from a data attr or use placeholder
  return document.querySelector('meta[name="onesignal-app-id"]')?.content || 'YOUR_ONESIGNAL_APP_ID';
}

// ----------------------------------------------------------
// Send a local browser notification
// ----------------------------------------------------------
window.sendLocalNotification = function(title, body, options = {}) {
  if (!('Notification' in window)) return;
  if (Notification.permission !== 'granted') return;

  const opts = {
    body,
    icon: '/weather/assets/images/icon-192.png',
    badge: '/weather/assets/images/icon-72.png',
    tag:   options.tag || 'sn-alert',
    requireInteraction: options.requireInteraction || false,
    ...options
  };

  if (navigator.serviceWorker?.controller) {
    navigator.serviceWorker.ready.then(reg => reg.showNotification(title, opts));
  } else {
    new Notification(title, opts);
  }
};
