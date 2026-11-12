const CACHE_NAME = 'bis-app-shell-v18';
let networkOnline = true;

readMetaSafe('online').then((value) => {
  if (value === '0' || value === '1') {
    networkOnline = value !== '0';
  }
});
const APP_SHELL = [
  '/manifest.webmanifest',
  '/js/resident-offline.js',
  '/style.css',
  '/dashboard-theme.css',
  '/favicon.ico',
  '/bacolod.png'
];

function blotterHome(path) {
  return String(path || '').indexOf('/admin/') === 0 ? '/admin/blotter' : '/secretary/blotter';
}

function isBlotterNavigationPath(path) {
  return /^\/(admin|secretary)\/blotter(?:\/|$)/.test(path || '');
}

function censusHome(path) {
  const match = String(path || '').match(/^\/(admin|secretary|captain|council)\//);
  return '/' + (match ? match[1] : 'secretary') + '/census';
}

function isCensusNavigationPath(path) {
  return /^\/(admin|secretary|captain|council)\/(?:census|household)(?:\/|$)/.test(path || '');
}

function offlineBlockedResponse(path) {
  const blotterPath = isBlotterNavigationPath(path);
  const censusPath = isCensusNavigationPath(path);
  const listUrl = blotterHome(path);
  const censusUrl = censusHome(path);
  const recovery = censusPath ? `
    <p style="margin-top:14px;">Saving a census record needs a connection. Open Census Records again when you are back online.</p>
    <p style="margin-top:16px;"><a href="${censusUrl}" style="display:inline-block;border-radius:8px;background:#16325c;color:#fff;padding:10px 14px;font-weight:700;text-decoration:none;">Back to Census Records</a></p>` : blotterPath ? `
    <p style="margin-top:14px;">Returning to the previous blotter page saved on this device.</p>
    <div style="display:flex;gap:8px;flex-wrap:wrap;margin-top:16px;">
      <button id="bisOfflineBack" type="button" style="border:0;border-radius:8px;background:#16325c;color:#fff;padding:10px 14px;font-weight:700;cursor:pointer;">Back to previous page</button>
      <a href="${listUrl}" style="display:inline-block;border-radius:8px;background:#eef2ff;color:#16325c;padding:10px 14px;font-weight:700;text-decoration:none;">Blotter reports</a>
    </div>
    <script>
      (function () {
        var listUrl = ${JSON.stringify(listUrl)};
        function currentPath() {
          return location.pathname.replace(/\\/index\\.php/, '').replace(/\\/+$/, '') || '/';
        }
        function readTrail() {
          try {
            var trail = JSON.parse(sessionStorage.getItem('bis_blotter_nav_trail') || '[]');
            return Array.isArray(trail) ? trail : [];
          } catch (error) {
            return [];
          }
        }
        function previousUrl() {
          var trail = readTrail();
          var current = currentPath();
          var at = trail.lastIndexOf(current);
          if (at > 0) return trail[at - 1];
          if (at === -1 && trail.length && trail[trail.length - 1] !== current) return trail[trail.length - 1];
          return '';
        }
        function go(url) {
          location.replace(url);
        }
        var target = previousUrl() || listUrl;
        var current = currentPath();
        var guard = '';
        try { guard = sessionStorage.getItem('bis_offline_nav_guard') || ''; } catch (error) {}
        if (target && target !== current && guard !== target) {
          try { sessionStorage.setItem('bis_offline_nav_guard', target); } catch (error) {}
          go(target);
          return;
        }
        var button = document.getElementById('bisOfflineBack');
        if (button) {
          button.addEventListener('click', function () {
            if (history.length > 1) {
              history.back();
              return;
            }
            go(listUrl);
          });
        }
      })();
    </script>` : '';

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
      <h1>${censusPath ? 'Census page needs a connection' : 'Page not available while offline'}</h1>
      <p>${censusPath
        ? 'Go back to Census Records and save again when the connection returns.'
        : 'Login and other modules stay online-only. Blotter cases and clearance requests opened while signed in on this device stay available offline. New reports and case updates are saved here and sent when the internet returns.'}</p>
      ${recovery}
    </div>
  </body>
  </html>`, {
    status: 200,
    headers: {
      'Content-Type': 'text/html; charset=utf-8',
      'Cache-Control': 'private, max-age=0'
    }
  });
}

function appPath(url) {
  return String(url.pathname || '').replace(/^\/index\.php/, '') || '/';
}

async function materializeResponse(response) {
  if (!response) {
    return response;
  }
  try {
    const headers = new Headers();
    response.headers.forEach((value, key) => {
      if (/^(set-cookie|set-cookie2|content-encoding|content-length|content-security-policy|report-to|nel)$/i.test(key)) {
        return;
      }
      try {
        headers.append(key, value);
      } catch (error) {
        // Skip headers the response constructor will not accept.
      }
    });
    headers.set('Cache-Control', 'private, max-age=0');
    headers.delete('Pragma');
    const body = await response.clone().blob();
    return new Response(body, {
      status: response.status || 200,
      statusText: response.statusText || 'OK',
      headers
    });
  } catch (error) {
    return response;
  }
}

async function asOfflineDocument(response) {
  const materialized = await materializeResponse(response);
  const type = materialized && materialized.headers ? (materialized.headers.get('Content-Type') || '') : '';
  if (!materialized || !/text\/html/i.test(type)) {
    return materialized;
  }
  try {
    let html = await materialized.text();
    if (html.indexOf('name="bis-offline-snapshot"') === -1) {
      html = html.replace('<head>', '<head><meta name="bis-offline-snapshot" content="1">');
    }
    const headers = new Headers(materialized.headers);
    headers.delete('Content-Length');
    return new Response(html, {
      status: materialized.status,
      statusText: materialized.statusText,
      headers
    });
  } catch (error) {
    return materialized;
  }
}

function fetchWithTimeout(request, ms) {
  const controller = new AbortController();
  const timer = setTimeout(() => controller.abort(), ms);
  return fetch(request, { signal: controller.signal }).finally(() => clearTimeout(timer));
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
    || /^\/(admin|captain|secretary|resident|sk|council)(?:\/|$)/.test(path);
}

function isOfflineModulePath(path) {
  return /^\/resident\/clearance\/?$/.test(path)
    || /^\/(admin|secretary)\/clearance\/?$/.test(path)
    || /^\/(admin|secretary)\/clearance\/request\/\d+\/?$/.test(path)
    || /^\/(admin|secretary)\/blotter\/?$/.test(path)
    || /^\/(admin|secretary)\/blotter\/new\/?$/.test(path)
    || /^\/(admin|secretary)\/blotter\/\d+\/?$/.test(path)
    || /^\/(admin|secretary)\/pii\/directory\/residents\/?$/.test(path);
}

function offlinePagesForRole(role) {
  if (role === 'resident') {
    return ['/resident/clearance'];
  }
  if (role === 'admin') {
    return ['/admin/clearance', '/admin/blotter', '/admin/blotter/new'];
  }
  if (role === 'secretary') {
    return ['/secretary/clearance', '/secretary/blotter', '/secretary/blotter/new'];
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
  const stored = await materializeResponse(response.clone());
  const variants = requestVariants(request);
  await Promise.all(variants.map((href) => cache.put(href, stored.clone()).catch(() => undefined)));
}

async function matchOfflinePage(request, userId) {
  if (!userId) {
    return null;
  }
  const cache = await caches.open(userCacheName(userId));
  const url = new URL(request.url);
  const path = appPath(url).replace(/\/+$/, '') || '/';
  const exactUrls = [
    url.origin + path + url.search,
    url.origin + path + '/' + url.search,
    url.origin + '/index.php' + path + url.search
  ];
  for (const candidate of exactUrls) {
    const hit = await cache.match(candidate, { ignoreVary: true }).catch(() => null);
    if (hit) {
      return hit;
    }
  }
  if (/\/pii\/directory\//.test(path)) {
    return null;
  }
  const looseUrls = [url.origin + path, url.origin + path + '/', url.origin + '/index.php' + path];
  for (const candidate of looseUrls) {
    const hit = await cache.match(candidate, { ignoreVary: true, ignoreSearch: true }).catch(() => null);
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

function readMetaSafe(key) {
  return readMeta(key);
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
  if (data.type === 'bis-network') {
    networkOnline = data.online !== '0' && data.online !== false;
    event.waitUntil(writeMeta('online', networkOnline ? '1' : '0'));
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
  event.waitUntil(Promise.all([
    caches.keys().then((keys) => Promise.all(
      keys.filter((key) => key.indexOf('bis-app-shell-') === 0 && key !== CACHE_NAME).map((key) => caches.delete(key))
    )),
    readMeta('online').then((value) => {
      if (value === '0' || value === '1') {
        networkOnline = value !== '0';
      }
    })
  ]));
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
          caches.open(CACHE_NAME).then((cache) => cache.put(request.url, copy)).catch(() => undefined);
        }
        return response;
      }).catch(async () => {
        const cached = await caches.match(request.url, { ignoreVary: true })
          || await caches.match(request, { ignoreVary: true });
        return cached || new Response('', { status: 504, statusText: 'Offline' });
      })
    );
    return;
  }

  if (url.origin !== self.location.origin) {
    return;
  }

  if (isSessionRequest(request)) {
    event.respondWith((async () => {
      const userId = await readMeta('userId');
      const modulePath = Boolean(userId && isOfflineModulePath(path));
      const cached = modulePath ? await matchOfflinePage(request, userId) : null;
      if (modulePath && isNavigationRequest && !networkOnline) {
        if (cached) {
          return asOfflineDocument(cached);
        }
        return offlineBlockedResponse(path);
      }
      try {
        const response = modulePath && isNavigationRequest
          ? await fetchWithTimeout(request, cached ? 2500 : 8000)
          : await fetch(request);
        if (modulePath && canCacheResponse(response) && !isAuthRedirectResponse(response)) {
          const toCache = response.clone();
          event.waitUntil((async () => {
            await storeOfflinePage(userId, request, toCache);
            if (/^\/(admin|secretary)\/blotter\/?$/.test(path)) {
              const prefix = path.indexOf('/admin/') === 0 ? '/admin' : '/secretary';
              await prefetchOfflinePages(userId, [prefix + '/blotter/new']);
            }
          })());
        }
        if (isNavigationRequest && response.redirected) {
          return materializeResponse(response);
        }
        return response;
      } catch (error) {
        if (cached) {
          return isNavigationRequest ? asOfflineDocument(cached) : materializeResponse(cached);
        }
        if (isNavigationRequest) {
          return offlineBlockedResponse(path);
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
        return offlineBlockedResponse(path);
      }
      return new Response('', { status: 503, statusText: 'Offline' });
    })
  );
});
