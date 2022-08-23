self.addEventListener('install', () => { self.skipWaiting(); });

self.addEventListener('push', function (event) {

    var data = event.data.json();

    const title = data.title;

    data.body = data.body.split("||");

    const options = {
        body: data.body[0],
        icon: data.icon,
        data: data.body[1]
    };

    self.registration.showNotification(title, options);

});

self.addEventListener('notificationclick', function(event) {

    var data = event.notification.data;

    if (data.length === 0) data = "/";

    event.notification.close();

    event.waitUntil(

        clients.matchAll({

            type: "window"

        }).then(function(clientList) {

            if (data) {

                for (var i = 0; i < clientList.length; i++) {

                    var client = clientList[i];

                    if (client.url === data && 'focus' in client)
                        return client.focus();

                }

                if (clients.openWindow) {
                    return clients.openWindow(data);
                }

            }

        })

    );

});