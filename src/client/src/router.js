import Vue from "vue";
import Router from "vue-router";

import extraRoutes from "./routes";

Vue.use(Router);



const router = new Router(
{
    routes:
    [
        ...extraRoutes,

        {
            path: "/logs",
            name: "logs",
            //component: Logs
            component: () => import(/* webpackChunkName: "logs" */ "./views/Logs"),
        },
        {
            path: "/settings",
            name: "settings",
            //component: Settings
            component: () => import(/* webpackChunkName: "settings" */ "./views/Settings"),
        },

        // And finally, the catch all route for HTTP 404 (Page Not Found) errors.
        {
            path: "*",
            name: "page-not-found",
            //component: PageNotFound
            component: () => import(/* webpackChunkName: "page-not-found" */ "./views/PageNotFound"),
        }
    ]

});

/*
router.beforeEach(function(to, from, next)
{
    if(to.meta.hasOwnProperty("conditionalRoute") && to.meta.conditionalRoute)
    {
        api.getEnvironment()
            .then($.proxy(function(env)
            {
                if(env.hasOwnProperty("mode") && env.mode === "development")
                    next();

                //next({ name: "page-not-found" })
                next(false);

            }, this));
    }

    next();
});
*/




export default router;
