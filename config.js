// img-loader plugins
const imageminGifsicle = require('imagemin-gifsicle')
const imageminMozjpeg = require('imagemin-mozjpeg')
const imageminPngquant = require('imagemin-pngquant')
const imageminSvgo = require('imagemin-svgo')

module.exports = {
  dir: {
    src: 'src',
    dest: 'wp-thumbnail-linkbox-shortcode',
    dist: 'wp-thumbnail-linkbox-shortcode'
  },
  proxy: '', // PHP使う場合バーチャルホストのURL

  options: {
    imagemin: [
      imageminGifsicle({
        interlaced: false
      }),
      imageminMozjpeg({
        quality: 80,
        progressive: true
      }),
      imageminPngquant({
        dithering: 0.5,
        speed: 2
      }),
      imageminSvgo({
        plugins: [
          {removeTitle: true},
          {convertPathData: false}
        ]
      })
    ]
  }
}
