/*
 * Simple AJAX module
 */
(function ($window) {
    // Exports
    $window.ajax = {
        get : get,
        post: post
    };

    // GET request
    function get(url, callback) {
        var req = getXhrRequest();

        req.open("GET", url, true);

        attachCallback(req, callback);
    }

    // POST request
    function post(url, data, callback) {
        var req = getXhrRequest();

        req.open("POST", url, true);
        req.setRequestHeader("X-Requested-With", "XMLHttpRequest");
        req.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
        req.send(data);

        attachCallback(req, callback);
    }

    // Get XHR object
    function getXhrRequest() {
        try {
            return new $window.XMLHttpRequest();
        } catch (e) {
            try {
                return new $window.ActiveXObject("Microsoft.XMLHTTP");
            } catch (e) {
                $window.alert("XHR not found!");
            }
        }
    }

    // Attach callback on request
    function attachCallback(req, callback) {
        req.onreadystatechange = function () {
            if (req.readyState == 4) {
                callback(req.status, req.statusText, req.responseText);
            }
        }
    }
})(window);