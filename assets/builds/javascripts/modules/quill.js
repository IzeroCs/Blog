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