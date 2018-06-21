var gulp         = require("gulp");
var gutil        = require("gulp-util");
var sass         = require("gulp-sass");
var plumber      = require("gulp-plumber");
var rename       = require("gulp-rename");
var livereload   = require("gulp-livereload");
var minifyCss    = require("gulp-cssnano");
var minifyJs     = require("gulp-uglify");
var cssbeautify  = require("gulp-cssbeautify");
var prettify     = require("gulp-js-prettify");
var wait         = require("gulp-wait");
var color        = require("gulp-color");
var concat       = require("gulp-concat");

gulp.task("themeProcess", function () {
    return gulp.src("assets/builds/themes/base.scss")
        .pipe(wait(500))
        .pipe(plumber({
            errorHandler: function(error) {
                console.log(error.toString());
                this.emit("end");
            }
        }))
        .pipe(sass())
        .pipe(plumber({
            errorHandler: function(error) {
                console.log(error.toString());
                this.emit("end");
            }
        }))
        .pipe(cssbeautify())
        .pipe(rename("style.css"))
        .pipe(gulp.dest("assets/themes"))
        .pipe(livereload());
});

gulp.task("themeQuillProcess", function () {
    return gulp.src("assets/builds/themes/quill/quill.scss")
    .pipe(wait(500))
    .pipe(plumber({
        errorHandler: function(error) {
            console.log(error.toString());
            this.emit("end");
        }
    }))
    .pipe(sass())
    .pipe(plumber({
        errorHandler: function(error) {
            console.log(error.toString());
            this.emit("end");
        }
    }))
    .pipe(cssbeautify())
    .pipe(rename("quill.css"))
    .pipe(gulp.dest("assets/themes"))
    .pipe(livereload());
});

gulp.task("fontIcomoonProcess", function () {
    return gulp.src("assets/builds/themes/icomoon/fonts/*.*")
               .pipe(gulp.dest("assets/themes/fonts"))
               .pipe(livereload())
               .on("end", function () {
                   console.log("Success clone font icomoon");
               });
});

gulp.task("fontProcess", function () {
    return gulp.src("assets/builds/themes/fonts/*.*")
               .pipe(gulp.dest("assets/themes/fonts"))
               .pipe(livereload())
               .on("end", function () {
                   console.log("Success clone font")
               });
});

gulp.task("jsAppProcess", function () {
    return gulp.src([
        "assets/builds/javascripts/app.js",
        "assets/builds/javascripts/modules/*.js",
        "assets/builds/javascripts/run.js"
    ]).pipe(concat("app.js"))
      .pipe(plumber({
          errorHandler: function(error) {
              console.log(error.toString());
              this.emit("end");
          }
      }))
      .pipe(gulp.dest("assets/javascripts"));
});

gulp.task("jsProcess", function () {
    return gulp.src("assets/builds/javascripts/librarys/*.js")
               .pipe(plumber({
                       errorHandler: function(error) {
                       console.log(error.toString());
                       this.emit("end");
                   }
               }))
               .pipe(minifyJs())
               .pipe(gulp.dest("assets/javascripts"));
});

gulp.task("watch", function() {
    livereload.listen();

    var watchs = {
        "themes": {
            "matchs": [
                "assets/builds/themes/*.scss",
                "assets/builds/themes/icomoon/*.scss"
            ],

            "task": [
                "themeProcess"
            ]
        },

        "quillthemes": {
            "matchs": [
                "assets/builds/themes/quill/*.scss"
            ],

            "task": [
                "themeQuillProcess"
            ]
        },

        "fonticomoon": {
            "matchs": [
                "assets/builds/themes/icomoon/fonts/*.*"
            ],

            "task": [
                "fontIcomoonProcess"
            ]
        },

        "font": {
            "matchs": [
                "assets/builds/themes/fonts/*.*"
            ],

            "task": [
                "fontProcess"
            ]
        },

        "jsapp": {
            "matchs": [
                "assets/builds/javascripts/app.js",
                "assets/builds/javascripts/modules/*.js",
                "assets/builds/javascripts/run.js"
            ],

            "task": [
                "jsAppProcess"
            ]
        },

        "js": {
            "matchs": [
                "assets/builds/javascripts/librarys/*.js"
            ],

            "task": [
                "jsProcess"
            ]
        }
    };

    for (var ars in watchs) {
        var wtc = watchs[ars];
        var mts = wtc["matchs"];
        var tsk = wtc["task"];

        gulp.watch(mts, tsk);
    }
});

gulp.task("default", [
    "themeProcess",
    "themeQuillProcess",
    "fontIcomoonProcess",
    "fontProcess",
    "jsAppProcess",
    "jsProcess",
    "watch"
]);