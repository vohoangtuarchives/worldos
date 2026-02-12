const mix = require('laravel-mix');
const path = require('path');

/*
 |--------------------------------------------------------------------------
 | Mix Asset Management
 |--------------------------------------------------------------------------
 |
 | Mix provides a clean, fluent API for defining some Webpack build steps
 | for your Laravel applications. By default, we are compiling the CSS
 | file for the application as well as bundling up all the JS files.
 |
 */

mix.js('resources/js/app.js', 'public/js')
    .sass('resources/sass/app.scss', 'public/css')
    .copy('node_modules/chart.js/dist/chart.min.js', 'public/js/chart.min.js')
    .copy('node_modules/bootstrap/dist/js/bootstrap.bundle.min.js', 'public/js/bootstrap.min.js')
    .copy('node_modules/bootstrap/dist/css/bootstrap.min.css', 'public/css/bootstrap.min.css')
    .copy('node_modules/jquery/dist/jquery.min.js', 'public/js/jquery.min.js')
    .copy('node_modules/axios/dist/axios.min.js', 'public/js/axios.min.js')
    .copy('node_modules/lodash/lodash.min.js', 'public/js/lodash.min.js')
    .copy('node_modules/moment/min/moment.min.js', 'public/js/moment.min.js')
    .copyDirectory('resources/images', 'public/images')
    .copyDirectory('resources/fonts', 'public/fonts')
    .options({
        processCssUrls: false,
        postCss: [
            require('autoprefixer'),
        ],
    })
    .sourceMaps()
    .version();

// Custom webpack configuration for better performance
mix.webpackConfig({
    resolve: {
        alias: {
            '@': path.resolve('resources/js'),
            '@components': path.resolve('resources/js/components'),
            '@services': path.resolve('resources/js/services'),
            '@utils': path.resolve('resources/js/utils'),
        },
    },
    optimization: {
        splitChunks: {
            chunks: 'all',
            cacheGroups: {
                vendor: {
                    test: /[\\/]node_modules[\\/]/,
                    name: 'vendors',
                    chunks: 'all',
                },
                common: {
                    name: 'common',
                    minChunks: 2,
                    chunks: 'all',
                    enforce: true,
                },
            },
        },
    },
});

// Production optimizations
if (mix.inProduction()) {
    mix.options({
        terser: {
            terserOptions: {
                compress: {
                    drop_console: true,
                    drop_debugger: true,
                },
            },
        },
    });
}

// Development settings
if (!mix.inProduction()) {
    mix.browserSync({
        proxy: 'localhost:8000',
        files: [
            'resources/views/**/*.blade.php',
            'public/js/**/*.js',
            'public/css/**/*.css',
        ],
        open: false,
        notify: false,
    });
}
