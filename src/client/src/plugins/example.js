/**
 * An example function to be imported and used by your application.
 *
 * @param Vue       The Vue instance.
 * @param options   Any Plugin options passed.
 */
export default function(Vue, options = {})
{
    Vue.prototype.$example = class
    {
        test()
        {
            console.log("Plugin: example.test()");
        }
    }
}
