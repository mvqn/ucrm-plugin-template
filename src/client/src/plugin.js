
import plugin from "./plugins"
import common from "./plugins/common";

export default function (Vue, options)
{
    Vue.use(common);

    Vue.use(plugin);
};
