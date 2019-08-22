
import Vue from "vue";
import Vuex from "vuex";

import stores from "./stores"

Vue.use(Vuex);

export default new Vuex.Store(
    {
        state:
        {
        },

        mutations:
        {
        },

        ...stores,
    }
);
