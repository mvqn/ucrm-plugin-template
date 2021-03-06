


// noinspection JSUnusedGlobalSymbols, JSUnusedLocalSymbols
const purgeCss = require("@fullhuman/postcss-purgecss")({

    // Specify the paths/globs to all of the template files in your project.
    content: [
        "./src/**/*.html",
        "./src/**/*.vue",
        "./src/**/*.jsx",
        // etc...
    ],

    // Include any special characters you're using in your class names here, default should be fine!
    defaultExtractor: content => content.match(/[\w-/:]+(?<!:)/g) || [],

    whitelistPatterns: [
        /* NOTE: These are necessary for the GrapesJS Editor, so keep them! */
        /gjs-/, /sp-/,
    ],

});


module.exports = {

    plugins: [
        //require("tailwindcss")("./tailwind.config.js"),
        require("autoprefixer"),
        ...process.env.NODE_ENV === "production" ? [purgeCss] : []
    ]

};
