importScripts('https://www.gstatic.com/firebasejs/10.12.2/firebase-app-compat.js');
importScripts('https://www.gstatic.com/firebasejs/10.12.2/firebase-messaging-compat.js');

firebase.initializeApp({
  apiKey: @json($apiKey),
  authDomain: @json($authDomain),
  projectId: @json($projectId),
  storageBucket: @json(config('services.firebase.web_storage_bucket')),
  messagingSenderId: @json($messagingSenderId),
  appId: @json($appId),
});

const messaging = firebase.messaging();

self.addEventListener('install', function (event) {
  self.skipWaiting();
});

self.addEventListener('activate', function (event) {
  event.waitUntil(self.clients.claim());
});

function notificationTarget(data) {
  data = data || {};
  if (data.order_id) {
    return '/admin/orders/' + data.order_id;
  }
  return '/admin/notifications';
}

// Background / other-tab / Chrome closed-but-running — show system notification.
messaging.onBackgroundMessage(function (payload) {
  const data = payload.data || {};
  const title = (payload.notification && payload.notification.title)
    || data.title
    || 'Paint Store';
  const body = (payload.notification && payload.notification.body)
    || data.body
    || '';

  return self.registration.showNotification(title, {
    body: body,
    icon: '/favicon.ico',
    badge: '/favicon.ico',
    data: Object.assign({}, data, { url: notificationTarget(data) }),
    requireInteraction: true,
  });
});

self.addEventListener('notificationclick', function (event) {
  event.notification.close();
  const url = (event.notification.data && event.notification.data.url)
    || '/admin/notifications';

  event.waitUntil(
    clients.matchAll({ type: 'window', includeUncontrolled: true }).then(function (clientList) {
      for (let i = 0; i < clientList.length; i++) {
        const client = clientList[i];
        if (client.url.indexOf(self.location.origin) === 0 && 'focus' in client) {
          return client.focus();
        }
      }
      if (clients.openWindow) {
        return clients.openWindow(url);
      }
    })
  );
});
