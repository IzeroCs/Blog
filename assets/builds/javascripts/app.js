var apps = (function () {
    var params = {};

    return {
        params: params,

        init: function (_params) {
            try {
                Object.keys(_params).forEach(function (value) {
                    params[value] = _params[value];
                });
            } catch (e) {

            }

            apps.disableDragDocument();
            apps.sidebar.init();
            apps.menu.init();
            apps.menu.unbindEvent();
            apps.menu.bindEvent();
            apps.search.init();
        },

        reloadCfsrToken: function () {
            var progress = apps.progress.getElement();

            if (progress !== null) {
                params.token_name = progress.getAttribute("data-token-name");
                params.token_value = progress.getAttribute("data-token-value");
            }
        }
    };
})();

apps.removeEvent = function(element, event, handle) {
    if (element === null)
        return;

    if (element.removeEventListener)
        element.removeEventListener(event, handle);
    else if (element.detachEvent)
        element.detachEvent(event, handle);
};

apps.addEvent = function(element, event, handle, remove) {
    if (element === null)
        return;

    if (remove)
        apps.removeEvent(element, event, handle);

    if (element.addEventListener)
        element.addEventListener(event, handle);
    else if (element.attachEvent)
        element.attachEvent(event, handle);
};

apps.initHistoryScript = function(script) {
    if (!window.history.pushState && !History.pushState && script !== null) {
        var head = getElementsByTagName("head");
        var src = apps.params.http_host + "/resource/" + apps.params.token_value + "/" + script;

        if (head.length > 0) {
            var history = document.createElement("script");
            history.type = "text/javascript";
            history.async = true;
            history.src = src;

            head[0].appendChild(history);
        }

        apps.addEvent(window, "keydown", function (e) {
            if ((e.which || e.keyCode) === 116 || (e.which || e.keyCode) === 82) {
                var href = window.location.href;
                var hastagPos = href.indexOf("#");

                if (hastagPos !== -1)
                    href = httpHost + "/" + href.substr(hastagPos + 1);

                window.location.href = href;

                e.preventDefault();
            } else {
                return true;
            }

            return false;
        }, true);

        script = null;
    }
};

apps.disableDragDocument = function() {
    apps.addEvent(document, "dragenter", function (e) {
        e.preventDefault();
        e.stopPropagation();
    });

    apps.addEvent(document, "dragover", function (e) {
        e.preventDefault();
        e.stopPropagation();
    });

    apps.addEvent(document, "dragleave", function (e) {
        e.preventDefault();
        e.stopPropagation();
    });

    apps.addEvent(document, "drop", function (e) {
        e.preventDefault();
        e.stopPropagation();
    });
};

