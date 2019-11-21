/////////////////////////////////////////////////////////////////////////////////////

// Requirement

const path = require('path')
const MiniCssExtractPlugin = require('mini-css-extract-plugin')
const FixStyleOnlyEntriesPlugin = require('webpack-fix-style-only-entries')
const OptimizeCssAssetsPlugin = require('optimize-css-assets-webpack-plugin')
const TerserPlugin = require('terser-webpack-plugin')
// const CompressionPlugin = require('compression-webpack-plugin')

// postcss plugins
const postCssCombineDuplicatedSelectors = require('postcss-combine-duplicated-selectors')
const cssDeclarationSorter = require('css-declaration-sorter')
const cssMqpacker = require('@lipemat/css-mqpacker')
const sortCssMediaQueries = require('sort-css-media-queries')
const autoprefixer = require('autoprefixer')

/////////////////////////////////////////////////////////////////////////////////////

// Config

const devMode = process.env.NODE_ENV !== 'production'
const config = require('./config')
const dir = config.dir
const options = config.options

/////////////////////////////////////////////////////////////////////////////////////

// Plugins

/////////////////////////////////////////////////////////////////////////////////////

let plugins = devMode ? [

] : [
  // new CompressionPlugin({
  //   cache: true
  // })
]

const commonPlugins = [
  new FixStyleOnlyEntriesPlugin({
    extensions: ['styl', 'css']
  }),
  new MiniCssExtractPlugin({
    filename: '[name]'
  })
]

plugins = plugins.concat(commonPlugins)

/////////////////////////////////////////////////////////////////////////////////////

// Exports

module.exports = {
  mode: process.env.NODE_ENV || 'development',

  entry: {
    'assets/css/style.css': path.resolve(__dirname, dir.src, 'stylus', 'style.styl')
  },

  output: {
    filename: 'assets/js/[name].js',
    path: path.resolve(__dirname, dir.dest)
  },

  resolve: {
    modules: [
      'node_modules',
      'modules'
    ]
  },

  optimization: {
    minimize: !devMode,
    minimizer: [
      new OptimizeCssAssetsPlugin(),
      new TerserPlugin({
        cache: true,
        parallel: true,
        terserOptions: {
          ecma: 6,
          compress: true,
          output: {
            beautify: false,
            comments: false
          }
        }
      })
    ]
  },

  plugins: plugins,

  externals: {
    jquery: 'jQuery'
  },

  module: {
    rules: [
      {
        test: /\.js$/,
        use: {
          loader: 'babel-loader',
          options: {
            presets: ['@babel/preset-env']
          }
        },
        exclude: /node_modules/
      },
      {
        test: /\.(styl|css)$/,
        use: [
          {
            loader: MiniCssExtractPlugin.loader
          },
          {
            loader: 'css-loader'
          },
          {
            loader: 'postcss-loader',
            options: {
              ident: 'postcss',
              plugins: () => [
                postCssCombineDuplicatedSelectors({
                  removeDuplicatedProperties: true
                }),
                cssMqpacker({
                  sort: sortCssMediaQueries
                }),
                cssDeclarationSorter({
                  order: 'smacss'
                }),
                autoprefixer({
                  grid: true,
                  browsers: [
                    'last 2 versions'
                  ]
                })
              ]
            }
          },
          // {
          //   loader: 'resolve-url-loader'
          // },
          {
            loader: 'stylus-loader',
            options: {
              'include css': true
            }
          }
        ]
      },
      {
        // test: /\.(jpe?g|png|gif|svg)$/,
        test: /\.(jpe?g|png|gif|svg|ico)(\?.+)?$/,
        use: [
          {
            loader: 'url-loader',
            options: {
              limit: 8192,
              name: path.join('assets', 'images', '[name].[ext]')
            }
          },
          {
            loader: 'img-loader', // options あとでやる
            options: {
              plugins: options.imagemin
            }
          }
        ]
      },
      {
        // test: /\.(ttf|otf|eot|woff(2)?)(\?[a-z0-9]+)?$/,
        test: /\.(eot|otf|ttf|woff2?|svg)(\?.+)?$/,
        use: [
          {
            loader: 'file-loader',
            options: {
              name: path.join('assets', 'fonts', '[name].[ext]')
            }
          }
        ]
      }
    ]
  }
}
