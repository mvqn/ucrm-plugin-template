
// NOTE: Be sure to "import" any custom routes here...
import demos from "./demos";
// ...

/**
 * Exports an array of routes to be included by your application's "router.js" file.
 */
export default
[
    // NOTE: Be sure to change this home route's "redirect" to an existing route of your choosing.
    {
        path: "/",
        name: "home",
        redirect: "/editor"
    },

    // NOTE: And then "map" your imported routes here...
    ...demos,
    // ...
];
