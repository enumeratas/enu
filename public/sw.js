const CACHE_NAME = 'bis-app-shell-v12';
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

function isOfflineModulePath(path) {
  return /^\/resident\/clearance\/?$/.test(path)
    || /^\/(admin|secretary)\/blotter\/?$/.test(path)
    || /^\/(admin|secretary)\/blotter\/new\/?$/.test(path);
}

function offlinePagesForRole(role) {
  if (role === 'resident') {
    return ['/resident/clearance'];
  }
  if (role === 'admin') {
    return ['/admin/blotter', '/admin/blotter/new'];
  }
  if (role === 'secretary') {
    return ['/secretary/blotter', '/secretary/blotter/new'];
  }
  return [];
}

function requestVariants(request) {
  const url = new URL(request.url || request, self.location.origin);
  const path = appPath(url).replace(/\/+$/, '') || '/';
  const origin = url.origin;
  return Array.from(new Set([
    request.url || (origin + path),
    origin + path,
    origin + path + '/',
    origin + '/index.php' + path,
    origin + '/index.php' + path + '/'
  ]));
}

async function storeOfflinePage(userId, request, response) {
  if (!userId || !canCacheResponse(response) || isAuthRedirectResponse(response)) {
    return;
  }
  const redirectedToLogin = response.redirected && /\/login(?:$|[?#])/i.test(response.url);
  if (redirectedToLogin) {
    return;
  }
  const cache = await caches.open(userCacheName(userId));
  const copy = response.clone();
  await cache.put(request, copy.clone()).catch(() => undefined);
  const variants = requestVariants(request);
  await Promise.all(variants.map((href) => cache.put(href, copy.clone()).catch(() => undefined)));
}

async function matchOfflinePage(request, userId) {
  if (!userId) {
    return null;
  }
  const cache = await caches.open(userCacheName(userId));
  const candidates = [request].concat(requestVariants(request));
  for (const candidate of candidates) {
    const hit = await cache.match(candidate, { ignoreSearch: true }).catch(() => null);
    if (hit) {
      return hit;
    }
  }
  return null;
}

async function prefetchOfflinePages(userId, pages) {
  if (!userId || !Array.isArray(pages)) {
    return;
  }
  for (const page of pages) {
    if (!page) {
      continue;
    }
    try {
      const request = new Request(new URL(page, self.location.origin).href, {
        credentials: 'same-origin',
        cache: 'reload'
      });
      const response = await fetch(request);
      await storeOfflinePage(userId, request, response);
    } catch (error) {
      // Keep any page that was already cached.
    }
  }
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
    const pages = Array.isArray(data.pages) && data.pages.length
      ? data.pages
      : offlinePagesForRole(data.role);
    event.waitUntil(Promise.all([
      writeMeta('userId', String(data.userId)),
      prefetchOfflinePages(String(data.userId), pages)
    ]));
  }
  if (data.type === 'bis-prefetch' && data.userId) {
    event.waitUntil(prefetchOfflinePages(String(data.userId), data.pages || offlinePagesForRole(data.role)));
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
      try {
        const response = await fetch(request);
        if (userId && isOfflineModulePath(path)) {
          const toCache = response.clone();
          event.waitUntil((async () => {
            await storeOfflinePage(userId, request, toCache);
            if (/^\/(admin|secretary)\/blotter\/?$/.test(path)) {
              const prefix = path.indexOf('/admin/') === 0 ? '/admin' : '/secretary';
              await prefetchOfflinePages(userId, [prefix + '/blotter/new']);
            }
          })());
        }
        return response;
      } catch (error) {
        if (userId && isOfflineModulePath(path)) {
          const cached = await matchOfflinePage(request, userId);
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
