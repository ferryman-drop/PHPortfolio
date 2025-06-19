module.exports = {
  plugins: [
    'postcss-import',
    'autoprefixer',
    'cssnano',
    'postcss-preset-env',
    ['postcss-custom-properties', { preserve: false }],
    ['postcss-minify-gradients'],
    ['postcss-merge-rules'],
    ['postcss-combine-duplicated-selectors', { removeDuplicatedProperties: true }]
  ],
} 