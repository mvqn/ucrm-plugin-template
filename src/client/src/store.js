
import Vue from "vue";
import Vuex from "vuex";

import stores from "./stores"
import common from "./stores/common"

Vue.use(Vuex);

export default new Vuex.Store(
    {
        ...common,

        ...stores,
    }
);
