const CACHE_NAME = 'bis-app-shell-v11';
const APP_SHELL = [
  '/manifest.webmanifest',
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
      <p>Login and other modules stay online-only. After you sign in, clearance requests and blotter reports opened on this device can be saved offline and sent when the internet returns.</p>
    </div>
  </body>
  </html>`, {
    status: 200,
    headers: { 'Content-Type': 'text/html; charset=utf-8' }
  });
}

function appPath(url) {
  return String(url.pathname || '').replace(/^\/index\.php/, '') || '/';
}

function canCacheResponse(response) {
  if (!response) {
    return false;
  }
  const status = Number(response.status || 0);
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
  const path = appPath(new URL(request.url));
  return path === '/login'
    || path === '/logout'
    || path.startsWith('/api/')
    || /^\/(admin|captain|secretary|resident|sk|council)(?:\/|$)/.test(path)
    || path.startsWith('/uploads/')
    || path.startsWith('/household-files/');
}

function isOfflineModulePage(url, request) {
  const navigation = request.mode === 'navigate' || request.destination === 'document';
  if (!navigation) {
    return false;
  }
  const path = appPath(url);
  return /^\/resident\/clearance\/?$/.test(path)
    || /^\/(admin|secretary)\/blotter\/?$/.test(path)
    || /^\/(admin|secretary)\/blotter\/new\/?$/.test(path);
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
  if (data.type === 'bis-user' && data.userId && ['resident', 'admin', 'secretary'].indexOf(data.role) !== -1) {
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
  const path = appPath(url);
  if (url.origin === self.location.origin && (path === '/sw.js' || path.includes('/notifications/poll') || path === '/public/concern/slots')) {
    return;
  }

  const isNavigationRequest = request.mode === 'navigate' || request.destination === 'document';
  const isStyleRequest = url.pathname.endsWith('.css');
  const isScriptRequest = url.pathname.endsWith('.js');
  const isFontAwesome = url.hostname === 'cdnjs.cloudflare.com' && url.pathname.indexOf('font-awesome') !== -1;

  if (isStyleRequest || isScriptRequest || isFontAwesome) {
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

  if (url.origin !== self.location.origin) {
    return;
  }

  if (isSessionRequest(request)) {
    event.respondWith((async () => {
      const userId = await readMeta('userId');
      const cacheName = userCacheName(userId);
      try {
        const response = await fetch(request);
        const redirectedToLogin = response.redirected && /\/login(?:$|[?#])/i.test(response.url);
        if (userId && isOfflineModulePage(url, request) && canCacheResponse(response) && !redirectedToLogin && !isAuthRedirectResponse(response)) {
          const copy = response.clone();
          caches.open(cacheName).then((cache) => cache.put(request, copy)).catch(() => undefined);
        }
        return response;
      } catch (error) {
        if (userId && isOfflineModulePage(url, request)) {
          const cached = await caches.match(request, { cacheName });
          if (cached) {
            return cached;
          }
        }
        if (isNavigationRequest) {
          return offlineBlockedResponse();
        }
        return new Response('', { status: 503, statusText: 'Offline' });
      }
    })());
    return;
  }

  event.respondWith(
    fetch(request).then((response) => {
      if (canCacheResponse(response) && !isAuthRedirectResponse(response)) {
        const copy = response.clone();
        caches.open(CACHE_NAME).then((cache) => cache.put(request, copy)).catch(() => undefined);
      }
      return response;
    }).catch(async () => {
      const cached = await caches.match(request);
      if (cached) {
        return cached;
      }
      if (isNavigationRequest) {
        return offlineBlockedResponse();
      }
      return new Response('', { status: 503, statusText: 'Offline' });
    })
  );
});
