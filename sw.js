const CACHE_NAME = 'pvi-cache-v1';
const urlsToCache = [
  './',
  './index.html',
  './dashboard.html',
  './tasks.html',
  './styles/header.css',
  './styles/main.css',
  './styles/modal.css',
  './styles/table.css',
  './styles/navigation.css',
  './styles/notifications.css',
  './styles/profile.css',
  './styles/skipmain.css',
  './scripts/validations.js',
  './scripts/burger-menu.js',
  './scripts/table.js',
  './scripts/nottification.js',
  './sources/avatar.png',
  './manifest.json'
];

// Install event - caches assets
self.addEventListener('install', event => {
  event.waitUntil(
    caches.open(CACHE_NAME)
      .then(cache => {
        console.log('Cache opened');
        // Instead of failing the whole installation when one file fails,
        // we'll use Promise.allSettled to cache as many files as possible
        return Promise.allSettled(
          urlsToCache.map(url => 
            fetch(url)
              .then(response => {
                if (!response.ok) {
                  throw new Error(`Failed to fetch ${url}: ${response.status} ${response.statusText}`);
                }
                return cache.put(url, response);
              })
              .catch(error => {
                console.warn(`Could not cache ${url}: ${error.message}`);
                return Promise.resolve(); // Don't fail the installation
              })
          )
        );
      })
      .catch(error => {
        console.error('Cache installation failed:', error);
      })
  );
});

// Fetch event - serve from cache, fall back to network
self.addEventListener('fetch', event => {
  event.respondWith(
    caches.match(event.request)
      .then(response => {
        // Cache hit - return response
        if (response) {
          return response;
        }

        // For manifest.json or any resource with specific problems,
        // we can add special handling
        if (event.request.url.endsWith('manifest.json')) {
          return handleManifestRequest(event.request.clone());
        }

        // Clone the request
        const fetchRequest = event.request.clone();

        return fetch(fetchRequest)
          .then(response => {
            // Check if valid response
            if (!response || response.status !== 200 || response.type !== 'basic') {
              return response;
            }

            // Clone the response
            const responseToCache = response.clone();

            caches.open(CACHE_NAME)
              .then(cache => {
                cache.put(event.request, responseToCache);
              })
              .catch(error => {
                console.error('Error caching response:', error);
              });

            return response;
          })
          .catch(error => {
            console.error('Fetch failed:', error, 'for URL:', event.request.url);
            
            // Return a custom fallback for specific file types
            const url = new URL(event.request.url);
            const extension = url.pathname.split('.').pop();
            
            if (extension === 'json' && url.pathname.includes('manifest.json')) {
              return new Response(JSON.stringify({
                name: "PVI Project",
                short_name: "PVI",
                description: "PVI Project Progressive Web App",
                start_url: "./index.html",
                display: "standalone",
                background_color: "#ffffff",
                theme_color: "#4285f4"
              }), {
                status: 200,
                statusText: 'OK',
                headers: new Headers({
                  'Content-Type': 'application/json'
                })
              });
            }
            
            // Default response for network errors
            return new Response('Network request failed. Please check your connection.', {
              status: 503,
              statusText: 'Service Unavailable',
              headers: new Headers({
                'Content-Type': 'text/plain'
              })
            });
          });
      })
  );
});

// Special handler for manifest.json to ensure it's always available
function handleManifestRequest(request) {
  return fetch(request)
    .then(response => {
      if (response && response.ok) {
        // If we got a good response, cache it and return it
        const responseToCache = response.clone();
        caches.open(CACHE_NAME)
          .then(cache => cache.put(request, responseToCache));
        return response;
      }
      
      throw new Error('Manifest fetch failed');
    })
    .catch(error => {
      console.warn('Manifest fetch error, providing fallback:', error);
      
      // Return a minimal valid manifest as fallback
      return new Response(JSON.stringify({
        name: "PVI Project",
        short_name: "PVI",
        description: "PVI Project Progressive Web App",
        start_url: "./index.html",
        display: "standalone",
        background_color: "#ffffff",
        theme_color: "#4285f4",
        icons: [
          {
            src: "./sources/avatar.png",
            sizes: "112x112",
            type: "image/png",
            purpose: "any"
          }
        ]
      }), {
        status: 200,
        headers: new Headers({
          'Content-Type': 'application/json'
        })
      });
    });
}

// Activate event - clean up old caches
self.addEventListener('activate', event => {
  const cacheWhitelist = [CACHE_NAME];

  event.waitUntil(
    caches.keys().then(cacheNames => {
      return Promise.all(
        cacheNames.map(cacheName => {
          if (cacheWhitelist.indexOf(cacheName) === -1) {
            return caches.delete(cacheName);
          }
        })
      );
    })
    .catch(error => {
      console.error('Cache activation failed:', error);
    })
  );
});