// ============================================================
// Surakshit Nepal — Service Worker (sw.js)
// Handles: Push Notifications, Cache-First for static assets
// ============================================================

const CACHE_NAME   = 'surakshit-nepal-v4';
const STATIC_URLS  = [
  '/',
  '/weather/',
  '/weather/index.php',
  '/weather/assets/css/style.css',
  '/weather/assets/js/app.js',
  '/weather/assets/js/weather.js',
];

// ----------------------------------------------------------
// Install — pre-cache static assets
// ----------------------------------------------------------
self.addEventListener('install', (e) => {
  e.waitUntil(
    caches.open(CACHE_NAME).then(cache => {
      return cache.addAll(STATIC_URLS).catch(() => {});
    }).then(() => self.skipWaiting())
  );
});

// ----------------------------------------------------------
// Activate — clean old caches
// ----------------------------------------------------------
self.addEventListener('activate', (e) => {
  e.waitUntil(
    caches.keys().then(keys =>
      Promise.all(keys.filter(k => k !== CACHE_NAME).map(k => caches.delete(k)))
    ).then(() => self.clients.claim())
  );
});

// ----------------------------------------------------------
// Fetch — network first for API, cache first for static
// ----------------------------------------------------------
self.addEventListener('fetch', (e) => {
  const url = new URL(e.request.url);

  // API calls — always network
  if (url.pathname.includes('/api/')) {
    e.respondWith(fetch(e.request).catch(() => new Response(
      JSON.stringify({ error: 'Offline' }), { headers: { 'Content-Type': 'application/json' } }
    )));
    return;
  }

  // Static — cache first
  e.respondWith(
    caches.match(e.request, { ignoreSearch: true }).then(cached => cached || fetch(e.request))
  );
});

// ----------------------------------------------------------
// Push notification handler
// ----------------------------------------------------------
self.addEventListener('push', (e) => {
  let data = {};
  try { data = e.data?.json() || {}; } catch { data = { title: 'Surakshit Nepal', body: e.data?.text() || 'New alert' }; }

  const title   = data.title   || 'Surakshit Nepal Alert';
  const options = {
    body:               data.body    || 'New disaster or weather alert.',
    icon:               '/weather/assets/images/icon-192.png',
    badge:              '/weather/assets/images/icon-72.png',
    tag:                data.tag     || 'sn-push',
    data:               { url: data.url || '/weather/alerts.php' },
    requireInteraction: data.severity === 'red',
    actions: [
      { action:'view',    title:'View Alert' },
      { action:'dismiss', title:'Dismiss'   },
    ],
  };

  e.waitUntil(self.registration.showNotification(title, options));
});

// ----------------------------------------------------------
// Notification click handler
// ----------------------------------------------------------
self.addEventListener('notificationclick', (e) => {
  e.notification.close();
  if (e.action === 'dismiss') return;

  const url = e.notification.data?.url || '/weather/alerts.php';
  e.waitUntil(
    clients.matchAll({ type:'window', includeUncontrolled:true }).then(list => {
      const existing = list.find(c => c.url.includes('/weather/'));
      if (existing) { existing.focus(); existing.navigate(url); }
      else clients.openWindow(url);
    })
  );
});
