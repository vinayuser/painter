@push('scripts')
<script>
(function () {
    const cfg = {
        apiKey: @json(config('services.firebase.web_api_key')),
        authDomain: @json(config('services.firebase.web_auth_domain')),
        projectId: @json(config('services.firebase.web_project_id')),
        storageBucket: @json(config('services.firebase.web_storage_bucket')),
        messagingSenderId: @json(config('services.firebase.web_messaging_sender_id')),
        appId: @json(config('services.firebase.web_app_id')),
        vapidKey: @json(config('services.firebase.web_vapid_key')),
    };
    const endpoint = @json($endpoint);
    const swUrl = @json(url('/firebase-messaging-sw.js'));
    const statusEl = document.getElementById('push-status');
    const btn = document.getElementById('enable-web-push');

    if (!btn) return;

    if (!('serviceWorker' in navigator) || !('PushManager' in window)) {
        if (statusEl) statusEl.textContent = 'This browser does not support push notifications';
        btn.disabled = true;
        return;
    }

    if (!cfg.apiKey || !cfg.projectId || !cfg.messagingSenderId || !cfg.appId) {
        if (statusEl) statusEl.textContent = 'Firebase web config missing in .env';
        btn.disabled = true;
        return;
    }

    if (!cfg.vapidKey) {
        if (statusEl) statusEl.textContent = 'Add FIREBASE_WEB_VAPID_KEY (Firebase → Cloud Messaging → Web Push certificates)';
        btn.disabled = true;
        return;
    }

    if (typeof firebase === 'undefined') {
        if (statusEl) statusEl.textContent = 'Firebase SDK failed to load';
        btn.disabled = true;
        return;
    }

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

    function waitForActiveWorker(registration) {
        if (registration.active) {
            return Promise.resolve(registration);
        }

        const worker = registration.installing || registration.waiting;
        if (!worker) {
            return navigator.serviceWorker.ready.then(function () {
                return registration;
            });
        }

        return new Promise(function (resolve, reject) {
            worker.addEventListener('statechange', function () {
                if (worker.state === 'activated') {
                    resolve(registration);
                } else if (worker.state === 'redundant') {
                    reject(new Error('Service Worker install failed'));
                }
            });
        });
    }

    if (Notification.permission === 'granted') {
        if (statusEl) statusEl.textContent = 'Chrome push is enabled for this browser';
    }

    btn.addEventListener('click', async function () {
        btn.disabled = true;
        if (statusEl) statusEl.textContent = 'Enabling…';

        try {
            const permission = await Notification.requestPermission();
            if (permission !== 'granted') {
                if (statusEl) statusEl.textContent = 'Permission denied — allow notifications in the address bar';
                btn.disabled = false;
                return;
            }

            let registration = await navigator.serviceWorker.register(swUrl, { scope: '/' });
            registration = await waitForActiveWorker(registration);
            await navigator.serviceWorker.ready;

            if (!registration.active) {
                throw new Error('Service Worker registered but is not active yet. Refresh and try again.');
            }

            const token = await messaging.getToken({
                vapidKey: cfg.vapidKey,
                serviceWorkerRegistration: registration,
            });

            if (!token) {
                throw new Error('No FCM token returned. Check VAPID key and Firebase web config.');
            }

            const res = await fetch(endpoint, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                },
                body: JSON.stringify({ fcm_token: token }),
            });

            const json = await res.json().catch(function () { return {}; });
            if (statusEl) {
                statusEl.textContent = res.ok
                    ? 'Enabled — you will get Chrome alerts even on other tabs or sites'
                    : (json.message || 'Failed to save token');
            }
        } catch (err) {
            console.error(err);
            if (statusEl) statusEl.textContent = err.message || 'Failed';
        } finally {
            btn.disabled = false;
        }
    });
})();
</script>
@endpush
