$ = require('gulp-load-plugins')()
gulp = require 'gulp'
runSequence = require 'run-sequence'

# Config ######################

dir =
  src: 'src'
  css: 'wp-thumbnail-linkbox-shortcode/css'
  scss: 'wp-thumbnail-linkbox-shortcode/scss'

sassOptions =
  style: 'expanded'
  sourcemap: false

pleeeseOptions =
  autoprefixer:
    browsers: ['last 2 versions']
  minifier: false
  sourcemaps: false
  sass: true

# Tasks #######################

# Sass
gulp.task 'sass', ->
  $.rubySass dir.src + '/**/*.scss', sassOptions

    .pipe $.pleeease pleeeseOptions
    .pipe gulp.dest dir.css
    .pipe $.pleeease {minifier: true}
    .pipe $.rename {suffix: '.min'}
    .pipe gulp.dest dir.css

# Copy
gulp.task 'copy', ->
  gulp
    .src dir.src + '/wp-thumbnail-linkbox-shortcode.scss'
    .pipe gulp.dest dir.scss

# build
gulp.task 'build', (cb) ->
  runSequence(
    'sass'
    'copy'
    cb
  )


#Watch
gulp.task 'default', ->
  gulp.watch dir.src + '/**/*.scss', ['sass']
