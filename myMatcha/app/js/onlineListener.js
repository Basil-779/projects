(function () {
    let path = window.location.pathname;
    if (!path.match(/account/)) {

    function Request() {
        sendRequest();
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
            body: 'online=1',
        })
    }

    setInterval(Request, 60000);
    }
})();