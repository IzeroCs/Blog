apps.menu = (function () {
    var button = null;

    function eventLinkClick() {
        apps.sidebar.hidden();
    }

    function addEventLinkClick() {
        var links = apps.sidebar.getSidebar().getElementsByTagName("a");

        for (var i = 0; i < links.length; ++i)
            apps.addEvent(links[i], "click", eventLinkClick, true);
    }

    function removeEventLinkClick() {
        var links = apps.sidebar.getSidebar().getElementsByTagName("a");

        for (var i = 0; i < links.length; ++i)
            apps.removeEvent(links[i], "click", eventLinkClick);
    }

    function eventClickButton(e) {
        if (apps.sidebar.getSidebar() === null)
            return e.stopPropagation();

        if (apps.sidebar.isSidebarHidden()) {
            apps.search.hidden();
            apps.sidebar.show();
            addEventLinkClick();
        } else {
            apps.sidebar.hidden();
            removeEventLinkClick();
        }

        e.stopPropagation();
        e.preventDefault();
    }

    function eventClickSidebar(e) {
        if (apps.sidebar.isSidebarHidden())
            return e.stopPropagation();

        if (apps.sidebar.getSidebar() === null || !apps.sidebar.getSidebar().style)
            return e.stopPropagation();

        apps.sidebar.hidden();
        removeEventLinkClick();
        e.stopPropagation();
        e.preventDefault();
    }

    function checkSidebarNotInclude() {
        if (apps.sidebar.getSidebar() === null)
            button.style.display = "none";
    }

    return {
        init: function () {
            button = document.querySelector("div#header span#menu");
            checkSidebarNotInclude();
        },

        bindEvent: function () {
            if (button !== null)
                apps.addEvent(button, "click", eventClickButton);

            if (apps.sidebar.getSidebar() !== null) {
                apps.addEvent(apps.sidebar.getSidebar(), "click", eventClickSidebar);
                apps.addEvent(apps.sidebar.getSidebar().querySelector("div.sidebar"), "click", function (e) {
                    e.stopPropagation();
                }, true);
            }
        },

        unbindEvent: function () {
            if (button !== null)
                apps.removeEvent(button, "click", eventClickButton);

            if (apps.sidebar.getSidebar() !== null)
                apps.removeEvent(apps.sidebar.getSidebar(), "click", eventClickSidebar);
        }
    };
})();