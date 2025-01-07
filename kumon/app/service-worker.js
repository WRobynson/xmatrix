self.addEventListener('install', (event) => {
  console.log('Service Worker: Instalado.');
  event.waitUntil(
      caches.open('meu-app-cache').then((cache) => {
          console.log('Service Worker: Cache criado.');
          return cache.addAll([
              '/',
              '/config/script.js',
              '/config/reset.css',
              '/config/estilos.css',
              '/logo/logo192.png',
              '/logo/logo512.png',
              '/logo/favicon.ico',
          ]).catch((error) => {
              console.error('Erro ao adicionar ao cache:', error);
          });
      })
  );
});
