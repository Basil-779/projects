(function () {
    let path = window.location.pathname;
    let mainDiv = document.getElementById('notifications');
    let notifCounter = document.getElementById('notification_counter');
    let notifBuffer = [];

    //if (!path.match(/account/)) {

        function Request() {
            sendRequest()
                .then(data => data ? parseNotifications(JSON.parse(data)) : false)
                //.then(data => console.log(data))
                .catch(error => console.error(error));
        }

        function sendRequest() {
            return fetch(path, {
                method: 'POST', // *GET, POST, PUT, DELETE, etc.
                mode: 'cors', // no-cors, cors, *same-origin
                cache: 'no-cache', // *default, no-cache, reload, force-cache, only-if-cached
                credentials: 'same-origin', // include, *same-origin, omit
                headers: {
                    //'Content-Type': 'application/json',
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                redirect: 'follow', // manual, *follow, error
                referrer: 'no-referrer', // no-referrer, *client
                body: 'listen=1',
            })
                .then(response => response.text());
        }
        
        function checkIfNotifExist() {

        }

        function parseNotifications(data) {
            if (data) {
                for (let i = 0; i < data.length; i++) {
                    if (mainDiv.childElementCount < 5) {
                        checkIfNotifExist(mainDiv, data[i]['id']);
                        mainDiv.appendChild(createNotification(data[i]['id'], data[i]['type']));
                    } else {
                        notifBuffer.push(data[i]);
                        notifCounter.textContent = "More notifications (" + notifBuffer.length + ")";
                    }
                }
            }
        }

        function createNotification(id, type) {
            let text;
            switch (type) {
                case 'like':
                    text = "Someone liked you";
                    break;
                case 'message':
                    text = "You have got a new message";
                    break;
                case 'likeback':
                    text = "You have a match with Paris!";
                    break;
                case 'unlike':
                    text = "Someone unliked you";
                    break;
                case 'report':
                    text = "Your profile has been checked by admin";
                    break;
            }

            let div = document.createElement('div');
            div.classList.add("alert", "alert-warning", "alert-dismissible", "fade", "show");
            div.setAttribute("role", "alert");
            div.setAttribute('data-id', id);

            let button = document.createElement('button');
            button.classList.add('close');
            button.setAttribute('type', "button");
            button.setAttribute('data-dismiss', "alert");
            button.setAttribute('aria-label', "Close");
            button.addEventListener('click', () => {
                closeNotification(button.parentElement.dataset.id)
            });

            let span = document.createElement('span');
            span.setAttribute('aria-hidden', "true");
            span.innerHTML = "&times;";

            button.appendChild(span);
            div.append(text);
            div.appendChild(button);

            return div;
        }

        function manageNotifBuffer() {
            if (notifBuffer.length) {
                let notif = notifBuffer.shift();
                mainDiv.appendChild(createNotification(notif['id'], notif['type']));
                notifCounter.textContent = notifBuffer.length ? ("More notifications (" + notifBuffer.length + ")") : "";
            }
        }

        function closeNotification(id) {
            fetch(path, {
                method: 'POST', // *GET, POST, PUT, DELETE, etc.
                mode: 'cors', // no-cors, cors, *same-origin
                cache: 'no-cache', // *default, no-cache, reload, force-cache, only-if-cached
                credentials: 'same-origin', // include, *same-origin, omit
                headers: {
                    //'Content-Type': 'application/json',
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                redirect: 'follow', // manual, *follow, error
                referrer: 'no-referrer', // no-referrer, *client
                body: 'read=' + encodeURIComponent(id),
            })
                .then(response => response.text())
                .then(data => {
                    if (data === "OK") {
                        manageNotifBuffer();
                    }
                })
                .catch(error => console.error(error));
        }

        setInterval(Request, 1000);
    //}
})();