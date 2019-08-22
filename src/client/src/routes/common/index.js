/**
 * Common Router Data
 *
 * @package     mvqn/ucrm-plugin-template
 * @author      Ryan Spaeth <rspaeth@mvqn.net>
 * @copyright   2019 Spaeth Technologies, Inc.
 */

/**
 * Exports any common routes used by all UCRM Plugins based on this template...
 */
export default
[
    {
        path: "/logs",
        name: "logs",
        component: () => import(/* webpackChunkName: "logs" */ "../../views/common/Logs"),
    },
    {
        path: "/settings",
        name: "settings",
        component: () => import(/* webpackChunkName: "settings" */ "../../views/common/Settings"),
    },

    // And finally, the catch all route for HTTP 404 (Page Not Found) errors.
    {
        path: "*",
        name: "page-not-found",
        component: () => import(/* webpackChunkName: "page-not-found" */ "../../views/common/PageNotFound"),
    }
]
