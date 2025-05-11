// This is the service worker with the Cache-first network

const CACHE = "lcmm-precache";
const precacheFiles = [
  /* Add an array of files to precache for your app */
  '/',
  '/index.html',
  '/static/js/main.chunk.js',
  '/static/js/0.chunk.js',
  '/static/js/bundle.js',
  '/static/css/main.chunk.css',
  '/manifest.json',
  '/favicon.ico',
  '/logo192.png',
  '/logo512.png'
];

self.addEventListener("install", function (event) {
  console.log("[PWA] Install Event processing");

  console.log("[PWA] Skip waiting on install");
  self.skipWaiting();

  event.waitUntil(
    caches.open(CACHE).then(function (cache) {
      console.log("[PWA] Caching pages during install");
      return cache.addAll(precacheFiles);
    })
  );
});

// Allow sw to control of current page
self.addEventListener("activate", function (event) {
  console.log("[PWA] Claiming clients for current page");
  event.waitUntil(self.clients.claim());
});

// If any fetch fails, it will look for the request in the cache and serve it from there
self.addEventListener("fetch", function (event) {
  if (event.request.method !== "GET") return;

  event.respondWith(
    fromCache(event.request).then(
      function (response) {
        // The response was found in the cache so we respond with it
        console.log("[PWA] Found in Cache", event.request.url, response);
        return response;
      },
      function () {
        // If it's not in the cache, then we fetch it
        return fetch(event.request)
          .then(function (response) {
            console.log("[PWA] No cache, fetching ", event.request.url);
            // Only cache valid responses
            if (!response || response.status !== 200 || response.type !== "basic") {
              return response;
            }

            if (event.request.url.includes('/api/')) {
              return response;
            }

            var responseClone = response.clone();
            caches.open(CACHE).then(function (cache) {
              cache.put(event.request, responseClone);
            });
            return response;
          })
          .catch(function (err) {
            console.log("[PWA] Fetch Error", err);
            return caches.match('/offline.html');
          });
      }
    )
  );
});

function fromCache(request) {
  // Check to see if you have it in the cache
  return caches.open(CACHE).then(function (cache) {
    return cache.match(request).then(function (matching) {
      if (!matching || matching.status === 404) {
        return Promise.reject("no-match");
      }
      return matching;
    });
  });
}