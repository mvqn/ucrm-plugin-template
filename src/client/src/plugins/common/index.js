

import api from "./api";

/**
 * Sets up any common plugins used by all Plugins...
 *
 * @param Vue
 * @param options
 */
export default function(Vue, options)
{
    Vue.use(api);
}
