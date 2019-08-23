/**
 * Common Plugin Data
 *
 * @package     mvqn/ucrm-plugin-template
 * @author      Ryan Spaeth <rspaeth@mvqn.net>
 * @copyright   2019 Spaeth Technologies, Inc.
 */

import api from "./api";

/**
 * Exports any common plugins used by all UCRM Plugins based on this template...
 *
 * @param Vue
 * @param options
 */
export default function(Vue, options)
{
    Vue.use(api);
}

// TEMPLATE2!
