apps.progress = (function () {
    var element = null;
    var interval = null;
    var count = 0;
    var current = 0;
    var time = 0;

    return {
        init: function () {
            if (element === null)
                element = document.querySelector("body > div#progressbar");

            return element;
        },

        updateCurrent: function (_current) {
            current = _current;
        },

        getCurrent: function () {
            return current;
        },

        updateTime: function (_time) {
            time = _time;
        },

        getTime: function () {
            return time;
        },

        updateCount: function (_count) {
            count = _count;
        },

        getCount: function () {
            return count;
        },

        getElement: function () {
            return apps.progress.init();
        },

        repaint: function () {
            apps.progress.init();

            if (interval !== null)
                clearInterval(interval, null);

            if (element.style.display === "none")
                element.style.width = "0%";

            element.style.display = "block";
            interval = setInterval(frame, time);

            function frame() {
                if (count >= current || count >= 100) {
                    clearInterval(interval);

                    if (count >= 100)
                        element.style.display = "none";
                } else {
                    count += 1;
                    element.style.width = count + "%";
                }
            }
        }
    };
})();