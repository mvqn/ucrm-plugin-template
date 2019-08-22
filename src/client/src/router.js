import Vue from "vue";
import Router from "vue-router";

import routes from "./routes";
import common from "./routes/common";

Vue.use(Router);

export default new Router(
{
    routes:
    [
        ...common,
        ...routes,
    ]

});

