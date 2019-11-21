const path = require('path')
const config = require('./config')
const dir = config.dir

const options = {
  ui: {
    port: 3000
  },
  files: [
    path.resolve(__dirname, dir.dest, '**', '*')
  ],
  notify: false,
  open: false,
  reloadDebounce: 0,
  reloadThrottle: 0
}

if(config.proxy) {
  options.proxy = {
    target: config.proxy
  }
} else {
  options.server = dir.dest
}

module.exports = options
