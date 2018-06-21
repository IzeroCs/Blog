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