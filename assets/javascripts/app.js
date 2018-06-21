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


apps.ajax = (function () {
    function processOptions(options, setups) {
        if (!options.params)
            options.params = {};

        if (!options.url)
            return false;

        if (!options.before)
            options.before = function (params, xhr) {
            };

        if (!options.end)
            options.end = function (params, xhr) {
            };

        if (!options.success)
            options.success = function (data, params, xhr) {
            };

        if (!options.error)
            options.error = function (params, xhr) {
            };

        if (!options.progress)
            options.progress = function (event, params, xhr) {
            };

        if (!options.uploadProgress)
            options.uploadProgress = function (event, params, xhr) {
            };

        if (!options.loadstart)
            options.loadstart = function (event, params, xhr) {
            };

        if (!options.loadend)
            options.loadend = function (event, params, xhr) {
            };

        if (!options.method)
            options.method = "GET";

        if (!options.async)
            options.async = true;

        setups.async = options.async;
        setups.url = options.url;
        setups.method = options.method;
        setups.params = options.params;

        setups.before = function (xhr) {
            options.before(setups.params, xhr);
        };

        setups.end = function (xhr) {
            options.end(setups.params, xhr);
        };

        setups.success = function (data, xhr) {
            options.success(data, setups.params, xhr);
        };

        setups.error = function (xhr) {
            options.error(setups.params, xhr);
        };

        setups.progress = function (event, xhr) {
            options.progress(event, setups.params, xhr);
        };

        setups.uploadProgress = function (event, xhr) {
            options.uploadProgress(event, setups.params, xhr);
        };

        setups.loadstart = function (event, xhr) {
            options.loadstart(event, setups.params, xhr);
        };

        setups.loadend = function (event, xhr) {
            options.loadend(event, setups.params, xhr);
        };

        return true;
    }

    function processEvent(options, setups, xhr) {
        var ready = false;

        xhr.onreadystatechange = function (e) {
            ready = true;
        };

        xhr.onloadstart = function (e) {
            setups.loadstart(e, xhr);
        };

        xhr.onprogress = function (e) {
            setups.progress(e, xhr);
        };

        xhr.upload.onprogress = function (e) {
            setups.uploadProgress(e, xhr);
        };

        xhr.onloadend = function (e) {
            if (ready) {
                if (xhr.readyState === 4 && xhr.status === 200)
                    setups.success(xhr.responseText, xhr);
                else
                    setups.error(xhr);
            }

            setups.loadend(e, xhr);
            setups.end(xhr);
        };

        setups.before(xhr);

        return xhr;
    }

    function processFormData(options, setups, xhr) {
        if (options.method === "POST") {
            var dataSend = new FormData();

            if (options.datas) {
                for (var key in options.datas)
                    dataSend.append(key, options.datas[key]);
            } else if (options.dataFormElement) {
                var arrays = [];
                var inputs = options.dataFormElement.getElementsByTagName("input");
                var textareas = options.dataFormElement.getElementsByTagName("textarea");
                var selects = options.dataFormElement.getElementsByTagName("select");
                var i = 0;

                if (inputs.length && inputs.length > 0) {
                    for (i = 0; i < inputs.length; ++i)
                        arrays.push(inputs[i]);
                }

                if (textareas.length && textareas.length > 0) {
                    for (i = 0; i < textareas.length; ++i)
                        arrays.push(textareas[i]);
                }

                if (selects.length && selects.length > 0) {
                    for (i = 0; i < selects.length; ++i)
                        arrays.push(selects[i]);
                }

                if (arrays.length && arrays.length > 0) {
                    for (i = 0; i < arrays.length; ++i) {
                        var input = arrays[i];
                        var name = null;
                        var value = null;
                        var type = null;
                        var tag = null;

                        if (input.name && input.name.length > 0)
                            name = input.name;

                        if (input.type && input.type.length > 0)
                            type = input.type;

                        if (input.value && input.value.length > 0)
                            value = input.value;

                        if (input.tagName)
                            tag = input.tagName.toLowerCase();

                        if (name !== null && tag !== null) {
                            if (value === null || (value.length && value.length <= 0))
                                value = "";

                            if (tag === "textarea" || tag === "select") {
                                dataSend.append(name, value);
                            } else if (type === "checkbox" || type === "radio") {
                                if (input.checked)
                                    dataSend.append(name, value);
                            } else if (type === "file") {
                                var files = input.files;

                                if (files.length && files.length > 0) {
                                    for (var j = 0; j < files.length; ++j)
                                        dataSend.append(input.name, files[j]);
                                }
                            } else {
                                dataSend.append(name, value);
                            }
                        }
                    }
                }
            }

            if (options.button && options.button.type && options.button.type === "submit") {
                var value = null;

                if (options.button.name) {
                    if (options.button.value)
                        value = options.button.value;
                    else if (options.button.tagName.toLowerCase && options.button.tagName.toLowerCase() === "button")
                        value = options.button.innerHTML;

                    dataSend.append(options.button.name, value);
                }
            }

            return dataSend;
        }

        return null;
    }

    return {
        open: function (options) {
            var xhr = apps.ajax.createXHR();
            var data = null;
            var setups = {};

            if (processOptions(options, setups)) {
                xhr = processEvent(options, setups, xhr);
                data = processFormData(options, setups, xhr);

                xhr.open(setups.method, setups.url, setups.async);
                xhr.send(data);
            }

            return xhr;
        },

        createXHR: function () {
            if (window.XMLHttpRequest)
                return new XMLHttpRequest();
            else
                return new ActiveXObject("Microsoft.XMLHTTP");
        }
    };
})();
apps.loaded = (function () {
    var elementButtonSubmit = null;

    var elementsA = [];
    var elementsForm = [];
    var elementsButton = [];

    function progressContent(url,
                             data,
                             xhr,
                             callbackRemoveEvent) {
        var titleTagBegin = "<title>";
        var titleTagEnd = "</title>";
        var titlePosBegin = data.indexOf(titleTagBegin);
        var titlePosEnd = data.indexOf(titleTagEnd);

        apps.progress.updateCurrent(80);
        apps.progress.repaint();

        var containerTagBegin = "<div id=\"master\">";
        var containerTagEnd = "</div>";
        var containerPosBegin = data.indexOf(containerTagBegin);
        var containerPosEnd = data.lastIndexOf(containerTagEnd);

        if (containerPosBegin === -1 || containerPosEnd === -1)
            return;

        if (titlePosBegin !== -1 && titlePosEnd !== -1) {
            var titleStr = data.substr(titlePosBegin + titleTagBegin.length, titlePosEnd - (titlePosBegin + titleTagBegin.length));
            var titleElement = document.getElementsByTagName("title");

            if (titleElement.length && titleElement.length > 0)
                titleElement[0].innerHTML = titleStr;
        }

        apps.progress.updateCurrent(84);
        apps.progress.repaint();

        if (typeof callbackRemoveEvent !== "undefined")
            callbackRemoveEvent();

        apps.progress.updateCurrent(86);
        apps.progress.repaint();

        var container = data.substr(containerPosBegin + containerTagBegin.length, containerPosEnd - (containerPosBegin + containerTagBegin.length));
        var containerElement = document.getElementById("master");
        var documentElement = document.documentElement;

        apps.progress.updateCurrent(90);
        apps.progress.repaint();

        containerElement.innerHTML = container;

        if (documentElement.pageYOffset)
            documentElement.pageYOffset = 0;

        if (documentElement.scrollTop)
            documentElement.scrollTop = 0;

        if (window.scrollTo)
            window.scrollTo(0, 0);

        apps.progress.updateCurrent(96);
        apps.progress.repaint();

        if (xhr.responseURL && xhr.responseURL !== null && xhr.responseURL.length > 0)
            url = xhr.responseURL;

        if (window.history.pushState) {
            window.history.pushState({
                path: url
            }, '', url);
        } else if (History.pushState) {
            History.pushState(null, null, url);
        }

        apps.progress.updateCurrent(98);
        apps.progress.repaint();

        onloads.runLoad();
        onloads.runInvoke();
    }

    function processUrl(url) {
        if (url.indexOf && url.indexOf(apps.params.http_host) === -1) {
            var strHttp = "http://";
            var strHttps = "https://";
            var posHttp = url.indexOf(strHttp);
            var posHttps = url.indexOf(strHttps);

            if (posHttp === -1 && posHttps === -1) {
                url = apps.params.http_host + "/" + url;
            } else {
                var posEndHttp = strHttp.length;

                if (posHttps === 0)
                    posEndHttp = strHttps.length;

                var posSeparatorEndDomain = url.indexOf("/", posEndHttp);

                if (posSeparatorEndDomain !== -1)
                    url = url.substr(posSeparatorEndDomain + 1);

                url = apps.params.http_host + "/" + url;
            }
        }

        return url;
    }

    function beforeHandle(xhr) {
        apps.progress.updateCount(0);
        apps.progress.updateCurrent(20);
        apps.progress.updateTime(20);
    }

    function endHandle(xhr) {
        apps.progress.updateCurrent(100);
        apps.progress.repaint();
    }

    function errorHandle(xhr) {
        console.log("Error");
        console.log(xhr);
    }

    function loadStartHandle(e, xhr) {
        apps.progress.repaint();
    }

    function progressHandle(e, xhr) {
        if (e.lengthComputable === false) {
            apps.progress.updateCurrent(80);
            apps.progress.updateTime(1);
        } else {
            var percent = (e.loaded / e.total * 60) + 20;

            if (percent > apps.progress.getCurrent())
                apps.progress.updateCurrent(percent);

            apps.progress.updateTime(apps.progress.getTime() - 3);
        }

        apps.progress.repaint();
    }

    function eventClickElementA(e) {
        if (!this.href)
            return;

        var href = processUrl(this.href);
        var request = apps.ajax.open({
            url: href,

            before: beforeHandle,
            error: errorHandle,
            loadstart: loadStartHandle,
            progress: progressHandle,

            end: function (xhr) {
                apps.loaded.reinitLoadTagA();
                endHandle(xhr);
            },

            success: function (data, xhr) {
                progressContent(href, data, xhr, function () {
                    for (var i = 0; i < elementsA.length; ++i)
                        apps.removeEvent(elementsA[i], "click", eventClickElementA);
                });
            }
        });

        e.stopPropagation();
        e.preventDefault();

        return false;
    }

    function eventClickElementButton() {
        elementButtonSubmit = this;
    }

    function eventSubmitElementForm() {
        var action = this.getAttribute("action");

        if (action !== null)
            action = processUrl(action);

        var request = apps.ajax.open({
            url: action,
            method: "POST",
            dataFormElement: this,
            button: elementButtonSubmit,

            before: beforeHandle,
            error: errorHandle,
            loadstart: loadStartHandle,
            progress: progressHandle,
            uploadProgress: progressHandle,

            end: function (xhr) {
                apps.loaded.reinitLoadTagForm();
                endHandle(xhr);
            },

            success: function (data, xhr) {
                progressContent(action, data, xhr, function () {
                    var i = 0;

                    for (i = 0; i < elementsButton.length; ++i)
                        apps.removeEvent(elementsButton[i], "click", eventClickElementButton);

                    for (i = 0; i < elementsForm.length; ++i)
                        apps.removeEvent(elementsForm[i], "submit", eventSubmitElementForm);
                });
            }
        });

        return false;
    }

    return {
        init: function () {

        },

        reinitLoadTagA: function () {
            elementsA = document.getElementsByTagName("a");

            for (var i = 0; i < elementsA.length; ++i) {
                var element = elementsA[i];

                if (!element.className || element.className.indexOf("not-loaded") === -1) {
                    element.setAttribute("onlick", "return false");

                    if (element !== null)
                        apps.addEvent(element, "click", eventClickElementA, true);
                }
            }
        },

        reinitLoadTagForm: function () {
            elementsForm = document.getElementsByTagName("form");
            elementsButton = [];

            var i = 0;
            var form = null;
            var inputs = document.getElementsByTagName("input");
            var buttons = document.getElementsByTagName("button");

            if (inputs.length && inputs.length > 0) {
                for (i = 0; i < inputs.length; ++i) {
                    var input = inputs[i];

                    if (input.type && input.type.toLowerCase() === "submit") {
                        if (input.form)
                            form = input.form;
                        else
                            form = null;

                        if (form === null || form.className.indexOf("not-loaded") === -1) {
                            apps.addEvent(input, "click", eventClickElementButton, true);

                            if (elementsButton.push)
                                elementsButton.push(input);
                        } else {
                            apps.removeEvent(input, "click", eventClickElementButton);
                        }
                    }
                }
            }

            if (buttons.length && buttons.length > 0) {
                for (i = 0; i < buttons.length; ++i) {
                    if (buttons[i].form)
                        form = buttons[i].form;
                    else
                        form = null;

                    if (form === null || form.className.indexOf("not-loaded") === -1) {
                        apps.addEvent(buttons[i], "click", eventClickElementButton, true);

                        if (elementsButton.push)
                            elementsButton.push(buttons[i]);
                    } else {
                        apps.removeEvent(buttons[i], "click", eventClickElementButton);
                    }
                }
            }

            for (i = 0; i < elementsForm.length; ++i) {
                form = elementsForm[i];

                if (form.className.indexOf("not-loaded") === -1) {
                    form.setAttribute("onsubmit", "return false");

                    if (form !== null)
                        apps.addEvent(form, "submit", eventSubmitElementForm, true);
                } else {
                    apps.removeEvent(form, "submit", eventSubmitElementForm);
                    form.removeAttribute("onsubmit");
                }
            }
        }
    };
})();
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
apps.quill = (function () {
    return {
        create: function (container) {
            return new Quill(container, {
                modules: {
                    toolbar: [
                        [{size: ["small", false, "large", "huge"]}],
                        ["bold", "italic", "underline", "strike"],
                        [{header: 1}, {header: 2}],
                        ["link", "image", "video"],
                        ["blockquote", "code-block"],
                        [{list: "ordered"}, {list: "bullet"}, {align: []}],
                        [{indent: "-1"}, {"indent": "+1"}],
                        [{script: "sub"}, {script: "super"}],
                        [{color: []}, {background: []}],
                        ["clean"]
                    ]
                },

                theme: "snow"
            });
        }
    }
})();
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
if (typeof onloads !== "undefined")
    onloads.addLoad(apps.init);