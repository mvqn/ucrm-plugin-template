


const manifest = require("../manifest.json");

/**
 * Gets the current plugin name from the manifest.json file.
 *
 * @returns {string}
 */
function pluginName()
{
    //return path.basename(path.resolve(__dirname, "../../"));
    return manifest.information.name;
}


// noinspection JSUnusedGlobalSymbols, JSUnusedLocalSymbols
module.exports = {

    /**
     * Set the public folder, depending on the environment.
     */
    publicPath: (
        process.env.NODE_ENV === "production"
            ? "/_plugins/" + pluginName() + "/public/"
            : "/public/"
    ),

    /**
     * Set the output directory for all of the processed files and folders.
     */
    outputDir: "../public/",

    /**
     * Set the output directory and name of the index.html file.
     */
    indexPath: "../index.html",

    /**
     * Append to the webpack configuration.
     * @param config
     */
    chainWebpack: function(config)
    {
        config.plugin("html")
            .tap(args => {
                args[0].template = "index.html";
                return args;
            });
    },

    /**
     * Configure the development server...
     */
    devServer: {

        /*
         * The host address, defaults to "localhost".
         * NOTE: This can be changed to suit your local development needs.
         */
        host: "localhost",

        /*
         * The host port, defaults to 8080.
         * NOTE: This can be changed to suit your local development needs.
         */
        port: 8080,

        /*
         * When true, the Hot Module Reloading (HMR) system will automatically recompile and refresh the browser.
         */
        hot: true,

        /*
         * When true, will automatically open a browser page when the development server startup completes.
         */
        open: false,

        //#region MIDDLEWARE

        /* *************************************************************************************************************
         * before()
         * -------------------------------------------------------------------------------------------------------------
         * Middleware to handle redirection of the public folder to the development server root.
         *
         * NOTE: As the new vue-cli-service uses it's publicPath too liberally, in my opinion, we use this to fix-up
         * the built-in mechanisms during development.
         *
         * Our index.html file lives one level up from the public/ folder and since relative paths include the public/
         * prefix already, we do not want the router to combine the two.  The solution to the problem is to keep the
         * publicPath setting as is and simply redirect requests looking for the index.html file inside the public/
         * folder to the root of the development server.  All relative paths will then work as expected and we will not
         * need to maintain independent production and development URLs.
         *
         * This actually mimics the behavior of our front-controller in production, so it keeps things consistent within
         * out template!
         */

        before: function(app, server)
        {
            // Fix-Up the URL when the route is a variant of the client-side entry point...
            // noinspection JSUnusedLocalSymbols
            app.get("/public",
                function(req, res, next)
                {
                    res.redirect("/");
                }
            );

            // Fix-Up the URL when the route is a variant of the server-side entry point...
            app.get("/public.php", function(req, res, next)
            {
                if(req.url === "/public.php?" || req.url === "/public.php?/" || req.url === "/public.php?/index.html")
                    res.redirect("/public.php");
                else
                    next();
            });
        },

        //#endregion

        proxy: {

            //#region REMOTE ASSETS

            /* *********************************************************************************************************
             * /assets, /dist
             * ---------------------------------------------------------------------------------------------------------
             * Here we proxy requests for "/assets" to an existing UCRM server, so that we can use Ubiquiti's built-in
             * assets during development.  This includes fonts, icons, images, stylesheets, scripts and more.
             *
             * NOTES:
             *   - Feel free to continue using my UCRM development server for this purpose, or add your own...
             *   - I no longer use the UCRM / UNMS assets in our template, as they were conflicting with some of the
             *     styling of the third-party components, but I will leave this here if anyone else wishes to make use
             *     of them in the future.
             */

            "/assets": {
                target: "https://ucrm.dev.mvqn.net/",
                changeOrigin: true
            },

            "/dist" : {
                target: "https://ucrm.dev.mvqn.net/",
                changeOrigin: true
            },

            //#endregion

            //#region STATIC ASSETS

            /* *********************************************************************************************************
             * /public.php
             * ---------------------------------------------------------------------------------------------------------
             * Proxy requests for the server-side front-controller from the Webpack Development Server to the PHP Web
             * Server as needed.
             *
             * NOTE: The PHP Web Server will need to be running, unless only the root "/public.php?/" URL is requested,
             * which simply redirects to the client-side application.
             */

            "/public.php": {

                target: "http://localhost:80/",

                changeOrigin: false,

                bypass: function(req, res, proxyOptions) {

                    // IF the requested URL is a variant of the server-side front-controller's root route...
                    //if(req.url === "/public.php?" || req.url === "/public.php?/")
                        // ...THEN redirect to the server-side "/public.php", to make URLs consistent!
                        //res.redirect("/public.php");

                    // IF the requested URL is the server-side front-controller "/public.php"...
                    if(req.url === "/public.php")
                        // ...THEN serve the client-side "/index.html" as if it were served by the server.
                        return "/index.html";

                    // OTHERWISE, proxy the specific request and let the server-side code handle it!
                },

            },

            //#endregion



        }


    },

};
