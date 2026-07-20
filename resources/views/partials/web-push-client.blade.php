{{-- Keeps the FCM service worker alive on every panel page so Chrome can alert even when this tab is not focused. --}}
@php
    $firebaseWebReady = filled(config('services.firebase.web_api_key'))
        && filled(config('services.firebase.web_project_id'))
        && filled(config('services.firebase.web_vapid_key'));
@endphp
@if($firebaseWebReady)
<script src="https://www.gstatic.com/firebasejs/10.12.2/firebase-app-compat.js"></script>
<script src="https://www.gstatic.com/firebasejs/10.12.2/firebase-messaging-compat.js"></script>
<script>
(function () {
    if (!('serviceWorker' in navigator) || !('Notification' in window)) return;

    const cfg = {
        apiKey: @json(config('services.firebase.web_api_key')),
        authDomain: @json(config('services.firebase.web_auth_domain')),
        projectId: @json(config('services.firebase.web_project_id')),
        storageBucket: @json(config('services.firebase.web_storage_bucket')),
        messagingSenderId: @json(config('services.firebase.web_messaging_sender_id')),
        appId: @json(config('services.firebase.web_app_id')),
        vapidKey: @json(config('services.firebase.web_vapid_key')),
    };
    const swUrl = @json(url('/firebase-messaging-sw.js'));

    if (!firebase.apps.length) {
        firebase.initializeApp({
            apiKey: cfg.apiKey,
            authDomain: cfg.authDomain,
            projectId: cfg.projectId,
            storageBucket: cfg.storageBucket,
            messagingSenderId: cfg.messagingSenderId,
            appId: cfg.appId,
        });
    }

    const messaging = firebase.messaging();

    // Always re-register SW so background push works after leaving the settings page.
    navigator.serviceWorker.register(swUrl, { scope: '/' }).catch(function (err) {
        console.warn('FCM service worker register failed', err);
    });

    // Foreground (this tab focused): show a Chrome notification ourselves.
    messaging.onMessage(function (payload) {
        if (Notification.permission !== 'granted') return;
        const title = (payload.notification && payload.notification.title)
            || (payload.data && payload.data.title)
            || 'Paint Store';
        const body = (payload.notification && payload.notification.body)
            || (payload.data && payload.data.body)
            || '';
        const n = new Notification(title, { body: body, icon: '/favicon.ico' });
        n.onclick = function () {
            window.focus();
            n.close();
        };
    });
})();
</script>
@endif
