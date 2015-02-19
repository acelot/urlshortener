/*
 * Application code
 */
(function ($window, $ajax) {
    // Exports
    $window.shortenUrl = shortenUrl;

    // Local variables
    var resultContainer = document.getElementById("result");

    // Shortener
    function shortenUrl(event) {
        preventDefault(event);

        var url = document.getElementsByName("url")[0].value;
        setResult("Shortening your url...");

        $ajax.post("/api/shorten", "url=" + encodeURIComponent(url), function (status, statusText, data) {
            if (status == 200) {
                var href = location.href + data;
                setResult("<span>" + href + "</span> <a target=\"_blank\" href=\"" + href + "\">open</a>");
            } else {
                console.error(status, statusText, data);
                setResult("<span class=\"error\">" + data + "</span>");
            }
        });

        return false;
    }

    function setResult(value) {
        resultContainer.innerHTML = value;
    }

    function preventDefault(event) {
        if (event.preventDefault) {
            event.preventDefault();
        } else {
            event.returnValue = false;
        }
    }
})(window, window.ajax);