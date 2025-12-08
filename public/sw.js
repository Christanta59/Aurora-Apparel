const CACHE_NAME = 'aurora-cache-v1';
const ASSETS = [
  '/public/index.php',
  '/public/tracking.php',
  '/public/assets/style.css',
  '/public/assets/icon-192.png',
  '/public/assets/icon-512.png'
];

self.addEventListener('install', (e) => {
  e.waitUntil(
    caches.open(CACHE_NAME).then(cache => cache.addAll(ASSETS))
  );
  self.skipWaiting();
});

self.addEventListener('activate', (e) => {
  e.waitUntil(self.clients.claim());
});

self.addEventListener('fetch', (e) => {
  if (e.request.method !== 'GET') return;
  e.respondWith(
    caches.match(e.request).then(cached => {
      return cached || fetch(e.request).then(resp => {
        return caches.open(CACHE_NAME).then(cache => {
          // Put dynamic responses into cache (optional)
          cache.put(e.request, resp.clone());
          return resp;
        });
      });
    }).catch(()=>caches.match('/public/index.php'))
  );
});
