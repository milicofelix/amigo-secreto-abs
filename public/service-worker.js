const CACHE_PREFIX = 'amigo-secreto-offline-';
const CACHE_NAME = `${CACHE_PREFIX}v1`;
const OFFLINE_PAGE = new URL('offline.html', self.registration.scope).href;
const OFFLINE_ICON = new URL('images/pwa-icon-192.png', self.registration.scope).href;
const OFFLINE_FILES = [OFFLINE_PAGE, OFFLINE_ICON];

self.addEventListener('install', (event) => {
    event.waitUntil((async () => {
        const cache = await caches.open(CACHE_NAME);
        await cache.addAll(OFFLINE_FILES.map((url) => new Request(url, {
            cache: 'reload',
            credentials: 'omit',
            redirect: 'error',
        })));
        await self.skipWaiting();
    })());
});

self.addEventListener('activate', (event) => {
    event.waitUntil((async () => {
        const names = await caches.keys();
        await Promise.all(names
            .filter((name) => name.startsWith(CACHE_PREFIX) && name !== CACHE_NAME)
            .map((name) => caches.delete(name)));
        await self.clients.claim();
    })());
});

self.addEventListener('fetch', (event) => {
    const request = event.request;

    if (request.method !== 'GET' || !request.url.startsWith(self.registration.scope)) {
        return;
    }

    if (request.mode === 'navigate') {
        event.respondWith(fetch(request, { cache: 'no-store' }).catch(async () => {
            const offlinePage = await caches.match(OFFLINE_PAGE, { cacheName: CACHE_NAME });
            return offlinePage ?? Response.error();
        }));
        return;
    }

    if (OFFLINE_FILES.includes(request.url)) {
        event.respondWith((async () => {
            const asset = await caches.match(request, { cacheName: CACHE_NAME });
            return asset ?? fetch(request);
        })());
    }
});

self.addEventListener('push', (event) => {
    let tag = 'amigo-secreto-sorteio';
    try {
        const payload = event.data?.json();
        if (/^sorteio-\d+$/.test(payload?.tag)) {
            tag = payload.tag;
        }
    } catch {
        // O aviso padrão também funciona sem dados adicionais.
    }

    event.waitUntil(self.registration.showNotification('Amigo Secreto ABS', {
        body: 'O sorteio foi realizado. Abra o app para consultar seu resultado.',
        icon: OFFLINE_ICON,
        tag,
    }));
});

self.addEventListener('notificationclick', (event) => {
    event.notification.close();
    event.waitUntil(self.clients.openWindow(new URL('meu-amigo', self.registration.scope).href));
});
