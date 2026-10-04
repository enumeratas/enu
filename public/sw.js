const CACHE_NAME = 'bis-app-shell-v10';
const APP_SHELL = [
  '/',
  '/login',
  '/manifest.webmanifest',
  '/js/offline-sync.js',
  '/js/resident-offline.js',
  '/style.css',
  '/dashboard-theme.css',
  '/favicon.ico',
  '/bacolod.png'
];

function offlineBlockedResponse() {
  return new Response(`<!doctype html>
  <html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Offline unavailable</title>
    <style>
      body { font-family: Arial, sans-serif; background: #f5f7fb; margin: 0; min-height: 100vh; display: grid; place-items: center; }
      .box { max-width: 560px; background: white; border-radius: 14px; box-shadow: 0 12px 26px rgba(29,36,72,.12); padding: 32px 28px; color: #1d2448; }
      h1 { margin: 0 0 12px; font-size: 28px; }
      p { margin: 0; line-height: 1.6; color: #475467; }
      .badge { display: inline-block; background: #fff3cd; color: #8a5b00; border: 1px solid #f1d89d; padding: 6px 10px; border-radius: 999px; font-size: 12px; font-weight: 700; margin-bottom: 12px; }
    </style>
  </head>
  <body>
    <div class="box">
      <div class="badge">OFFLINE MODE</div>
      <h1>Page not available while offline</h1>
      <p>This page is not allowed during offline mode. Your current session stays active, and any request or report you create on cached pages will be saved and synced automatically when the internet is restored.</p>
    </div>
  </body>
  </html>`, {
    status: 200,
    headers: { 'Content-Type': 'text/html; charset=utf-8' }
  });
}

function canCacheResponse(response) {
  if (!response) {
    return false;
  }

  const status = Number(response.status || 0);
  if (Number.isNaN(status)) {
    return false;
  }

  return status >= 200 && status < 300;
}

function isAuthRedirectResponse(response) {
  if (!response) {
    return false;
  }

  const status = Number(response.status || 0);
  if (status >= 300 && status < 400) {
    return true;
  }

  const location = response.headers && response.headers.get ? response.headers.get('location') : null;
  return Boolean(location && /\/login(?:$|[?#])/i.test(location));
}

function isSessionRequest(request) {
  const url = new URL(request.url);
  return url.pathname === '/login'
    || url.pathname === '/logout'
    || url.pathname.startsWith('/api/')
    || /^\/(admin|captain|secretary|resident|sk|council)(?:\/|$)/.test(url.pathname)
    || url.pathname.startsWith('/uploads/')
    || url.pathname.startsWith('/household-files/');
}

function isResidentDocument(url, request) {
  const navigation = request.mode === 'navigate' || request.destination === 'document';
  return navigation
    && /^\/(resident|sk)(?:\/|$)/.test(url.pathname)
    && !url.pathname.includes('/pii/');
}

function metaDb() {
  return new Promise((resolve, reject) => {
    const request = indexedDB.open('bis-sw-meta', 1);
    request.onupgradeneeded = () => request.result.createObjectStore('meta');
    request.onsuccess = () => resolve(request.result);
    request.onerror = () => reject(request.error);
  });
}

function readMeta(key) {
  return metaDb().then((db) => new Promise((resolve) => {
    const request = db.transaction('meta', 'readonly').objectStore('meta').get(key);
    request.onsuccess = () => resolve(request.result || '');
    request.onerror = () => resolve('');
  })).catch(() => '');
}

function writeMeta(key, value) {
  return metaDb().then((db) => new Promise((resolve) => {
    const request = db.transaction('meta', 'readwrite').objectStore('meta').put(value, key);
    request.onsuccess = () => resolve();
    request.onerror = () => resolve();
  })).catch(() => undefined);
}

function userCacheName(userId) {
  return 'bis-offline-' + String(userId || '');
}

self.addEventListener('message', (event) => {
  const data = event.data || {};
  if (data.type === 'bis-user' && data.userId && (data.role === 'resident' || data.role === 'sk')) {
    event.waitUntil(writeMeta('userId', String(data.userId)));
  }
  if (data.type === 'bis-logout' && data.userId) {
    event.waitUntil(Promise.all([
      caches.delete(userCacheName(data.userId)),
      writeMeta('userId', '')
    ]));
  }
});

self.addEventListener('install', (event) => {
  event.waitUntil(
    caches.open(CACHE_NAME).then((cache) => cache.addAll(APP_SHELL)).catch(() => undefined)
  );
  self.skipWaiting();
});

self.addEventListener('activate', (event) => {
  event.waitUntil(
    caches.keys().then((keys) => Promise.all(
      keys.filter((key) => key.indexOf('bis-app-shell-') === 0 && key !== CACHE_NAME).map((key) => caches.delete(key))
    ))
  );
  self.clients.claim();
});

self.addEventListener('fetch', (event) => {
  const { request } = event;
  if (request.method !== 'GET') return;

  const url = new URL(request.url);
  if (url.origin !== self.location.origin) return;
  if (url.pathname === '/sw.js' || url.pathname.includes('/notifications/poll') || url.pathname === '/public/concern/slots') return;

  const isNavigationRequest = request.mode === 'navigate' || request.destination === 'document';
  const isStyleRequest = url.pathname.endsWith('.css');
  const isScriptRequest = url.pathname.endsWith('.js');

  // Always prefer the latest stylesheets and scripts so a cached copy cannot freeze the app.
  if (isStyleRequest || isScriptRequest) {
    event.respondWith(
      fetch(request).then((response) => {
        if (canCacheResponse(response)) {
          const copy = response.clone();
          caches.open(CACHE_NAME).then((cache) => cache.put(request, copy)).catch(() => undefined);
        }
        return response;
      }).catch(() => caches.match(request))
    );
    return;
  }

  // Official pages always come from the server. A resident or SK page that was
  // opened while online is kept only for that signed-in user so it can open offline.
  if (isSessionRequest(request)) {
    event.respondWith((async () => {
      const userId = await readMeta('userId');
      const cacheName = userCacheName(userId);
      try {
        const response = await fetch(request);
        const redirectedToLogin = response.redirected && /\/login(?:$|[?#])/i.test(response.url);
        if (userId && isResidentDocument(url, request) && canCacheResponse(response) && !redirectedToLogin && !isAuthRedirectResponse(response)) {
          const copy = response.clone();
          caches.open(cacheName).then((cache) => cache.put(request, copy)).catch(() => undefined);
        }
        return response;
      } catch (error) {
        if (userId && isResidentDocument(url, request)) {
          const cached = await caches.match(request, { cacheName });
          if (cached) {
            return cached;
          }
        }
        if (request.mode === 'navigate' || request.destination === 'document') {
          return offlineBlockedResponse();
        }
        return new Response('', { status: 503, statusText: 'Offline' });
      }
    })());
    return;
  }

  if (isNavigationRequest && navigator.onLine) {
    event.respondWith(
      fetch(request).then((response) => {
        if (canCacheResponse(response)) {
          const copy = response.clone();
          caches.open(CACHE_NAME).then((cache) => cache.put(request, copy)).catch(() => undefined);
        }

        if (isAuthRedirectResponse(response)) {
          return response;
        }

        return response;
      }).catch(() => {
        const fallback = caches.match(request) || caches.match('/');
        return fallback || offlineBlockedResponse();
      })
    );
    return;
  }

  event.respondWith(
    caches.match(request).then((cached) => {
      if (cached && !isAuthRedirectResponse(cached)) {
        return cached;
      }

      return fetch(request).then((response) => {
        const copy = response.clone();
        if (canCacheResponse(response)) {
          caches.open(CACHE_NAME).then((cache) => cache.put(request, copy)).catch(() => undefined);
        }
        return response;
      }).catch(() => {
        if (isNavigationRequest) {
          const fallback = caches.match(request) || caches.match('/');
          return fallback || offlineBlockedResponse();
        }

        return caches.match('/');
      });
    })
  );
});
