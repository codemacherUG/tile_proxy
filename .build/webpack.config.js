const webpack = require('webpack');
const path = require('path');
const MiniCssExtractPlugin = require("mini-css-extract-plugin");
const CssMinimizerPlugin = require("css-minimizer-webpack-plugin");
const TerserPlugin = require("terser-webpack-plugin");

var colors = require('colors');

let entry = {
  output: {
    path: path.resolve(__dirname, '../Resources/Public/JavaScript'),
    filename: '[name].js',
    library: '[name]',
  },

  devtool: false,
  plugins: [
    new MiniCssExtractPlugin({
      filename: "../Css/[name].css", // change this RELATIVE to your output.path!
      chunkFilename: "[id].css",
    }),
    new webpack.SourceMapDevToolPlugin({
      filename: '[file].map[query]'
    }),
  ],
  stats: {
    colors: true,
    hash: false,
    version: false,
    timings: true,
    assets: false,
    chunks: false,
    modules: false,
    reasons: false,
    children: false,
    source: false,
    errors: true,
    errorDetails: true,
    warnings: true,
    publicPath: false
  },
  resolve: {
    // Add `.ts` and `.tsx` as a resolvable extension.
    extensions: [".ts", ".tsx", ".js"],
    // Add support for TypeScripts fully qualified ESM imports.
    extensionAlias: {
     ".js": [".js", ".ts"],
     ".cjs": [".cjs", ".cts"],
     ".mjs": [".mjs", ".mts"]
    },
    modules: [path.join(__dirname, 'node_modules'), 'node_modules'],
  },
  module: {
    rules: [
      {
        test: /\.tsx?$/,
        use: 'ts-loader',
        exclude: /node_modules/,
      },
  /*    {
        test: /\.m?js$/,
        exclude: /(node_modules|bower_components)/,
        use: {
          loader: 'babel-loader',
          options: {
            presets: ['@babel/preset-env']
          }
        }
      },*/
      {
        test: /\.(sa|sc|c)ss$/,
        use: [
          MiniCssExtractPlugin.loader,
          {
            loader: 'css-loader',
            options: {
              url: true,
            }
          },

          {
            loader: 'postcss-loader',
            options: {
              postcssOptions: {
                plugins: [
                  [
                    "postcss-preset-env",
                    {
                      // Options
                    },
                  ],
                ],
              },
            }
          },
          {
            loader: 'sass-loader',
          }
        ]
      },
      {
        test: /\.(svg|eot|woff|woff2|ttf)$/,
        type: 'asset/resource',
        generator: {
          filename: '../GeneratedResources/[name][ext]'
        }
      },

      {
        test: /.(jpg|jpeg|png)$/,
        use: [
          {
            loader: 'file-loader',
            options: {
              outputPath: '../GeneratedResources',
              publicPath: '../GeneratedResources',

              name(resourcePath, resourceQuery) {
                return '[path][name].[ext]';
              }

            }
          }
        ]
      }
    ]
  },
  optimization: {
    minimize: true,
    minimizer: [
      new CssMinimizerPlugin(),
      new TerserPlugin()
    ],
  },

};

let modules = { ...entry };

module.exports = function (env, args) {
  modules.entry = {
    BoundingBoxMapElement: [path.resolve(__dirname, '../Resources/Private/TypeScript/BoundingBoxMapElement.ts'), path.resolve(__dirname, '../Resources/Private/Scss/BoundingBoxMap.scss')],
    MapCreator: [path.resolve(__dirname, '../Resources/Private/TypeScript/MapCreator.ts'), path.resolve(__dirname, '../Resources/Private/Scss/MapCreator.scss')],
  };
  modules.output.path = path.resolve(__dirname, '../Resources/Public/JavaScript');
  modules.externals = {};
  return [modules];
};
