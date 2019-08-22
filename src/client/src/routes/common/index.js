/**
 * Exports any common routes used by all Plugins...
 */
export default
[
    {
        path: "/logs",
        name: "logs",
        component: () => import(/* webpackChunkName: "logs" */ "../../views/Logs"),
    },
    {
        path: "/settings",
        name: "settings",
        component: () => import(/* webpackChunkName: "settings" */ "../../views/Settings"),
    },

    // And finally, the catch all route for HTTP 404 (Page Not Found) errors.
    {
        path: "*",
        name: "page-not-found",
        component: () => import(/* webpackChunkName: "page-not-found" */ "../../views/PageNotFound"),
    }
]
