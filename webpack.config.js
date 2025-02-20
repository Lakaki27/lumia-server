const Encore = require('@symfony/webpack-encore');

// Manually configure the runtime environment if not already configured yet by the "encore" command.
// It's useful when you use tools that rely on webpack.config.js file.
if (!Encore.isRuntimeEnvironmentConfigured()) {
    Encore.configureRuntimeEnvironment(process.env.NODE_ENV || 'dev');
}

Encore
    // directory where compiled assets will be stored
    .setOutputPath('public/build/')
    // public path used by the web server to access the output path
    .setPublicPath('/build')
    // only needed for CDN's or subdirectory deploy
    //.setManifestKeyPrefix('build/')

    /**
     * JS ENTRIES
     */

    // GLOBAL
    .addEntry("main_js", "./assets/js/main.js")
    .addEntry("navbar_js", "./assets/js/navbar.js")

    // LIBRARIES
    .addEntry("jsbarcode", "./node_modules/jsbarcode/dist/JsBarcode.all.min.js")

    // USERS
    .addEntry("users_users_js", "./assets/js/pages/users/users.js")
    .addEntry("users_details_js", "./assets/js/pages/users/details.js")
    .addEntry("users_add_js", "./assets/js/pages/users/add.js")

    // PRODUCTS
    .addEntry("products_products_js", "./assets/js/pages/products/products.js")
    .addEntry("products_details_js", "./assets/js/pages/products/details.js")
    .addEntry("products_add_js", "./assets/js/pages/products/add.js")

    // ROLES
    .addEntry("roles_roles_js", "./assets/js/pages/roles/roles.js")
    .addEntry("roles_details_js", "./assets/js/pages/roles/details.js")
    .addEntry("roles_add_js", "./assets/js/pages/roles/add.js")

    // EMBEDDED CLIENTS
    .addEntry("embedded-clients_embedded-clients_js", "./assets/js/pages/embedded_clients/embedded_clients.js")
    .addEntry("embedded-clients_details_js", "./assets/js/pages/embedded_clients/details.js")
    .addEntry("embedded-clients_add_js", "./assets/js/pages/embedded_clients/add.js")

    /**
     * CSS ENTRIES
     */
    .addStyleEntry("main_css", "./assets/styles/main.css")

    // When enabled, Webpack "splits" your files into smaller pieces for greater optimization.
    .splitEntryChunks()

    // enables the Symfony UX Stimulus bridge (used in assets/bootstrap.js)
    .enableStimulusBridge('./assets/controllers.json')

    // will require an extra script tag for runtime.js
    // but, you probably want this, unless you're building a single-page app
    .enableSingleRuntimeChunk()

    /*
     * FEATURE CONFIG
     *
     * Enable & configure other features below. For a full
     * list of features, see:
     * https://symfony.com/doc/current/frontend.html#adding-more-features
     */
    .cleanupOutputBeforeBuild()
    .enableSourceMaps(!Encore.isProduction())
    // enables hashed filenames (e.g. app.abc123.css)
    .enableVersioning(Encore.isProduction())

    // configure Babel
    // .configureBabel((config) => {
    //     config.plugins.push('@babel/a-babel-plugin');
    // })

    // enables and configure @babel/preset-env polyfills
    .configureBabelPresetEnv((config) => {
        config.useBuiltIns = 'usage';
        config.corejs = '3.38';
    })

    .enablePostCssLoader()

    // enables Sass/SCSS support
    //.enableSassLoader()

    // uncomment if you use TypeScript
    //.enableTypeScriptLoader()

    // uncomment if you use React
    //.enableReactPreset()

    // uncomment to get integrity="..." attributes on your script & link tags
    // requires WebpackEncoreBundle 1.4 or higher
    //.enableIntegrityHashes(Encore.isProduction())

    // uncomment if you're having problems with a jQuery plugin
    //.autoProvidejQuery()
;

module.exports = Encore.getWebpackConfig();
