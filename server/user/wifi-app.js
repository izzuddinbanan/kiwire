document.addEventListener("DOMContentLoaded", () => {

    const applicationServerKey = "BCmti7ScwxxVAlB7WAyxoOXtV7J8vVCXwEDIFXjKvD-ma-yJx_eHJLdADyyzzTKRGb395bSAtxlh4wuDycO3Ih4";

    if (!('serviceWorker' in navigator)) {
        return;
    }

    if (!('PushManager' in window)) {
        return;
    }

    if (!('showNotification' in ServiceWorkerRegistration.prototype)) {
        return;
    }

    if (Notification.permission === 'denied') {
        return;
    }

    navigator.serviceWorker.register("wifi-sw.js").then(() => {}, e => {});

    function urlBase64ToUint8Array(base64String) {
        const padding = '='.repeat((4 - base64String.length % 4) % 4);
        const base64 = (base64String + padding)
            .replace(/\-/g, '+')
            .replace(/_/g, '/');

        const rawData = window.atob(base64);
        const outputArray = new Uint8Array(rawData.length);

        for (let i = 0; i < rawData.length; ++i) {
            outputArray[i] = rawData.charCodeAt(i);
        }
        return outputArray;
    }

    function push_subscribe() {

        navigator.serviceWorker.ready
        .then(serviceWorkerRegistration => serviceWorkerRegistration.pushManager.subscribe({
            userVisibleOnly: true,
            applicationServerKey: urlBase64ToUint8Array(applicationServerKey),
        }))
        .then(subscription => {
            return push_sendSubscriptionToServer(subscription, 'POST');
        })
        .then(subscription => subscription)
        .catch(e => {});
    }

    function push_unsubscribe() {

        navigator.serviceWorker.ready
        .then(serviceWorkerRegistration => serviceWorkerRegistration.pushManager.getSubscription())
        .then(subscription => {
            if (!subscription) {
                return;
            }

            return push_sendSubscriptionToServer(subscription, 'DELETE');
        })
        .then(subscription => subscription.unsubscribe())
        .then()
        .catch(e => {});

    }

    function push_sendSubscriptionToServer(subscription, method) {

        const key = subscription.getKey('p256dh');
        const token = subscription.getKey('auth');
        const contentEncoding = (PushManager.supportedContentEncodings || ['aesgcm'])[0];

        return fetch('/user/push/', {
            method,
            body: JSON.stringify({
                endpoint: subscription.endpoint,
                publicKey: key ? btoa(String.fromCharCode.apply(null, new Uint8Array(key))) : null,
                authToken: token ? btoa(String.fromCharCode.apply(null, new Uint8Array(token))) : null,
                contentEncoding,
            }),
        }).then(() => subscription);

    }

    push_subscribe();

});





