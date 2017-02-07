$ = require('gulp-load-plugins')()
gulp = require 'gulp'

# Config ######################

dir =
  src: 'src'
  release: 'wp-thumbnail-linkbox-shortcode/css'

sassOptions =
  style: 'expanded'
  require: ['bourbon']
  sourcemap: false

pleeeseOptions =
  autoprefixer:
    browsers: ['last 2 versions', 'android 4.1']
  minifier: false
  sourcemaps: false
  sass: true

# Tasks #######################

# Sass
gulp.task 'sass', ->
  $.rubySass dir.src + '/**/*.scss', sassOptions

    .pipe $.pleeease pleeeseOptions
    .pipe gulp.dest dir.release
    .pipe $.pleeease {minifier: true}
    .pipe $.rename {suffix: '.min'}
    .pipe gulp.dest dir.release

# Copy
gulp.task 'copy', ->
  gulp
    .src dir.src + '/wp-thumbnail-linkbox-shortcode.scss'
    .pipe gulp.dest dir.release




#Watch
gulp.task 'default', ->
  gulp.watch dir.src + '/**/*.scss', ['sass']
