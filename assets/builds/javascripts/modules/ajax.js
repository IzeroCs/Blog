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