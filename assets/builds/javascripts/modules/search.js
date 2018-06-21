apps.search = (function () {
    var elementBoxSearch = null;
    var elementResultList = null;
    var elementEmptyResult = null;
    var elementInput = null;
    var elementForm = null;
    var elementButton = null;
    var elementButtonToggle = null;
    var elementFormSearch = null;
    var intervalSearch = null;
    var intervalTime = 500;
    var ajaxSearch = null;

    function removeAllResult()
    {
        elementResultList.innerHTML = "";
    }

    function drawAlert(alert) {
        var element = document.createElement("li");
        var text = document.createElement("span");

        text.innerHTML = alert.msg;
        elementResultList.appendChild(element);
        element.classList.add("alert");
        element.classList.add(alert.type);
        element.appendChild(text);
    }

    function drawEmpty() {
        elementResultList.appendChild(elementEmptyResult);
    }

    function drawListArticle(datas) {
        var length = datas.length;
        var article = null;
        var link = null;
        var itemElement = null;
        var linkElement = null;
        var iconElement = null;
        var textElement = null;

        for (var i = 0; i < length; ++i) {
            article = datas[i];
            link = apps.params.search_rewrite_urls.article;
            link = link.replace("{$id}", article.id);
            link = link.replace("{$seo}", article.seo);

            itemElement = document.createElement("li");
            iconElement = document.createElement("span");
            linkElement = document.createElement("a");
            textElement = document.createElement("span");

            itemElement.classList.add("item");
            iconElement.classList.add("icomoon");
            iconElement.classList.add("icon-news");

            linkElement.href = link;
            textElement.textContent = article.title;

            linkElement.appendChild(textElement);
            itemElement.appendChild(iconElement);
            itemElement.appendChild(linkElement);
            elementResultList.appendChild(itemElement);
        }
    }

    function drawResult(data) {
        var datas = data.datas;
        var alert = data.alert;

        removeAllResult();

        if (alert.msg !== null && alert.msg.length > 0)
            drawAlert(alert);
        else if (datas.article.length <= 0)
            drawEmpty();
        else
            drawListArticle(datas.article);
    }

    function handleSearch() {
        var datas = {
            keyword: elementInput.value,
            search: elementButton,
            type: "json"
        };

        datas[apps.params.token_name] = apps.params.token_value;

        ajaxSearch = apps.ajax.open({
            url: apps.params.http_host + "/" + apps.params.search_url,
            method: "POST",
            datas: datas,

            error: function (params, xhr) {
                console.log("Error", xhr);
            },

            success: function (data, params, xhr) {
                try {
                    data = JSON.parse(data);
                } catch (e) {
                    data = {
                        datas: {
                            article: {}
                        },

                        alert: {
                            msg: e.toString(),
                            type: apps.params.alert_types.danger
                        }
                    }
                }
                
                drawResult(data);
            }
        });
    }

    function eventInput(e) {
        if (ajaxSearch !== null && ajaxSearch.abort)
            ajaxSearch.abort();

        if (intervalSearch !== null)
            clearTimeout(intervalSearch);

        var value = elementInput.value.trim();

        if (value.length <= 1) {
            removeAllResult();
            drawEmpty();
        } else if (e.data && e.data === " " && value.length > 1) {
            handleSearch();
        } else if (e.type === "click" || (e.type === "keydown" && e.keyCode === 13)) {
            handleSearch();
        } else if (value.length > 1) {
            intervalSearch = setTimeout(handleSearch, intervalTime);
        }

        e.preventDefault();
        e.stopPropagation();
    }

    function eventKeydownInput(e) {
        if (e.keyCode === 13)
            eventInput(e);
    }

    function eventToggleButton(e) {
        if (apps.search.isSearchHidden()) {
            apps.sidebar.hidden();
            apps.search.show();
        } else {
            apps.search.hidden();
            removeAllResult();
            drawEmpty();
        }

        e.stopPropagation();
    }

    function eventClickBoxSearch(e) {
        apps.search.hidden();
        e.stopPropagation();
    }

    function eventClickFormSearch(e)
    {
        e.stopPropagation();
    }

    function eventClickButton(e) {
        eventInput(e);
        e.stopPropagation();
    }

    return {
        init: function () {
            apps.search.reinit();
        },

        hidden: function () {
            if (elementBoxSearch !== null) {
                elementBoxSearch.style.display = "none";
                elementButtonToggle.parentNode.classList.remove("show-search");
            }
        },

        show: function () {
            if (elementBoxSearch !== null) {
                elementBoxSearch.style.display = "block";
                elementButtonToggle.parentNode.classList.add("show-search");
                elementInput.focus();
            }
        },

        isSearchHidden: function () {
            if (elementBoxSearch === null)
                return false;

            var display = elementBoxSearch.style.display;

            return (display === "" || display === "none");
        },

        reinit: function () {
            elementBoxSearch = document.querySelector("div#box-search");

            if (elementBoxSearch !== null) {
                elementResultList = elementBoxSearch.querySelector("ul.result");
                elementEmptyResult = elementResultList.querySelector("li.empty").cloneNode(true);
                elementInput = elementBoxSearch.querySelector("input#search-input");
                elementForm = elementInput.form;
                elementButton = elementBoxSearch.querySelector("input#search-input + button[name=search]");
                elementButtonToggle = document.querySelector("li.search > span#button-toggle-search");
                elementFormSearch = elementBoxSearch.querySelector("div.form-search");

                apps.search.hidden();
                elementForm.setAttribute("onsubmit", "return false");
                apps.addEvent(elementInput, "input", eventInput, true);
                apps.addEvent(elementInput, "keydown", eventKeydownInput, true);
                apps.addEvent(elementButton, "click", eventClickButton, true);
                apps.addEvent(elementButtonToggle, "click", eventToggleButton, true);
                apps.addEvent(elementFormSearch, "click", eventClickFormSearch, true);
                apps.addEvent(elementBoxSearch, "click", eventClickBoxSearch, true);
            }
        }
    };
})();