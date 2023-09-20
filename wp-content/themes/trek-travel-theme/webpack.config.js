const path = require('path');

module.exports = {
  entry: [
    path.resolve(__dirname, 'src/main.js'),
    path.resolve(__dirname, 'src/scss/main.scss')
  ],
  output: {
    path: path.resolve(__dirname, 'assets'),
    filename: 'main.js'
  },
  module: {
    rules: [
      {
        test: /\.js$/,
        exclude: /node_modules/,
        use: []
      },
      {
        test: /\.scss$/,
        use: [
          {
            loader: 'style-loader'
          },
          {
            loader: 'file-loader',
            options: {
              outputPath: 'css',
              name: 'main.css'
            }
          },
          {
            loader: 'postcss-loader',
            options: {
              postcssOptions: {
                plugins: () => [
                  require('autoprefixer')
                ]
              }
            }
          },
          {
            loader: 'sass-loader'
          },
        ],
      },
    ],
  },
}