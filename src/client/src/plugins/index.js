
// NOTE: Be sure to "import" any custom plugins here...
import example from "./example";
// ...

/**
 * Exports a function that will be called by Vue.use() from your application's "main.js" file.
 *
 * @param Vue       The Vue instance.
 * @param options   Any Plugin options passed.
 */
export default function(Vue, options = {})
{
    // NOTE: And then "use" your imported plugins here...
    Vue.use(example);
    // ...
}

// TEMPLATE3!
