const gulp = require('gulp')
const path = require('path')

const config = require('./config')
const dir = config.dir
const src = {
  stylus: [
    path.resolve(dir.src, 'stylus', '**', '*.styl')
  ]
}
const dist = {
  stylus: path.resolve(__dirname, dir.dist, 'assets', 'stylus')
}

gulp.task('copy', function (cb) {
  const items = ['stylus']

  items.forEach(function (item) {
    gulp
      .src(src[item])
      .pipe(gulp.dest(dist[item]))
  })
  cb()
})
