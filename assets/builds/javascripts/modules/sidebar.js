apps.sidebar = (function () {
    var sidebar = null;

    return {
        init: function () {
            sidebar = document.querySelector("div#wrapper div#sidebar-wrapper");
        },

        hidden: function() {
            if (sidebar !== null)
                sidebar.style.display = "none";
        },

        show: function () {
            if (sidebar !== null)
                sidebar.style.display = "block";
        },

        isSidebarHidden: function () {
            if (sidebar === null)
                return false;

            var display = sidebar.style.display;

            return (display === "" || display === "none");
        },

        getSidebar: function () {
            return sidebar;
        }
    };
})();