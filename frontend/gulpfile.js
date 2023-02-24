const { src, dest, series, watch } = require("gulp");

const browserSync = require("browser-sync").create();
const del = require("del");
const scss = require("gulp-sass")(require("sass"));
const autoprefixer = require("gulp-autoprefixer");
const cssMinify = require("gulp-clean-css");
const jsMinify = require("gulp-terser");
const webpack = require("webpack-stream");
const concat = require("gulp-concat");
const webp = require("gulp-webp");
const addVersionString  = require("gulp-version-number");

const versionConfig = {
  "value": "%DT%",
  "append": {
    "key": "v",
    "to": ["css", "js"],
  },
}

// Copy HTML Files
function copyHTML() {
  return src("./src/**/*.html")
      .pipe(addVersionString(versionConfig))
      .pipe(dest("./dist"));
}

// Copy Image Files
function copyImages() {
  return src("./src/img/*").pipe(dest("./dist/img"));
}

// Clean dist folder
function distClean() {
  return del(["./dist"]);
}

// Copy Fonts
function fontsTask() {
  return src("./src/fonts/**/*")
      .pipe(dest("./dist/fonts/"))
}

function styles() {
  return src("./src/scss/**/*.scss", { sourcemaps: true })
    .pipe(scss())
    .pipe(autoprefixer("last 2 versions", "> 1%"))
    .pipe(cssMinify())
    .pipe(dest("./dist/css/", { sourcemaps: "." }))
}

function scripts() {
  return src("./src/js/**/*.js", { sourcemaps: true })
    .pipe(webpack(require("./webpack.config.js")))
    .pipe(jsMinify())
      .pipe(dest("./dist/js/"))
}

function dialogOnly() {
  return src("./src/js/app-dialog-only.js", { sourcemaps: true })
      .pipe(webpack(require("./webpack.config.js")))
      .pipe(jsMinify())
      .pipe(dest("./dist/js/"))
}

function vendors() {
  return src([
    "node_modules/jquery/dist/jquery.slim.js",
    "node_modules/shariff/dist/shariff.min.js",
  ])
    .pipe(jsMinify())
    .pipe(concat("vendors.min.js"))
    .pipe(dest("./dist/js/"));
}

function livePreview(done) {
  browserSync.init({
    server: {
      baseDir: "./dist",
      hot: true,
    },
    port: 5000,
  });
  done();
}

// Triggers Browser reload
function previewReload(done) {
  browserSync.reload();
  done();
}

function watchTask() {
  watch(["./src/**/*.html"], series(copyHTML, previewReload));
  watch(["./src/scss/**/*.scss"], series(styles, previewReload));
  watch(["./src/js/**/*.js"], series(scripts, previewReload));
  watch(["./src/img/*"], series(copyImages, previewReload));
}

exports.default = series(
  copyHTML,
  copyImages,
  fontsTask,
  styles,
  scripts,
  livePreview,
  watchTask
);
exports.build = series(
  distClean,
  copyHTML,
  copyImages,
  fontsTask,
  styles,
  scripts
);

exports.dialog = series(dialogOnly);
