import Vue from "vue";
import Router from "vue-router";

Vue.use(Router);

const router = new Router({

    routes: [
        {
            path: "/",
            name: "home",
            redirect: "/editor"
        },
        {
            path: "/editor",
            name: "editor",
            //component: EditorDemo
            component: () => import(/* webpackChunkName: "editor" */ "../views/EditorDemo"),
        },
        {
            path: "/query",
            name: "query-builder",
            //component: QueryBuilderDemo
            component: () => import(/* webpackChunkName: "query-builder" */ "../views/QueryBuilderDemo"),
        },
        {
            path: "/designer",
            name: "html-designer",
            //component: HtmlDesignerDemo
            component: () => import(/* webpackChunkName: "html-designer" */ "../views/HtmlDesignerDemo"),
        },
        {
            path: "/logs",
            name: "logs",
            //component: Logs
            component: () => import(/* webpackChunkName: "logs" */ "../views/Logs"),
        },
        {
            path: "/settings",
            name: "settings",
            //component: Settings
            component: () => import(/* webpackChunkName: "settings" */ "../views/Settings"),
        },

        //
        // Add any new routes here...
        //


        // And finally, the catch all route for HTTP 404 (Page Not Found) errors.
        {
            path: "*",
            name: "page-not-found",
            //component: PageNotFound
            component: () => import(/* webpackChunkName: "page-not-found" */ "../views/PageNotFound"),
        }
    ]
});

/*
router.beforeEach(function(to, from, next)
{
    console.log(to);
    //to.fullPath = to.fullPath.replace("?%2F", "");

    next();
});
*/



export default router;
