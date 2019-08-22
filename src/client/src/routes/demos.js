/**
 * An example array to be imported and mapped to your application's primary router..
 */
export default
[
    // NOTE: Include any routes for your router here...
    {
        path: "/editor",
        name: "editor",
        component: () => import(/* webpackChunkName: "editor" */ "../views/EditorDemo"),
    },
    {
        path: "/query",
        name: "query-builder",
        component: () => import(/* webpackChunkName: "query-builder" */ "../views/QueryBuilderDemo"),
    },
    {
        path: "/designer",
        name: "html-designer",
        component: () => import(/* webpackChunkName: "html-designer" */ "../views/HtmlDesignerDemo"),
    },

    // ...
];
