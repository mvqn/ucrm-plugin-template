


import grapes from "grapesjs";
import loadTypes from "./types";
//import loadComponents from "./components";
import loadTraits from "./traits";
import loadBlocks from "./blocks";

import { pluginName, dynamicField } from "./consts";

export default grapes.plugins.add(pluginName, function(editor, options = {})
{
    //let opts = options;

    //const data = options.data;

    let defaults = {

        blocks: [
            dynamicField.name,
            // Add other plugin related blocks here...
        ],


        // Default style
        defaultStyle: true,

        fieldClassPrefix: dynamicField.classPrefix,
        fieldLabel: dynamicField.label,
        fieldCategory: dynamicField.category,

        fieldObjectDefault: "user",
        fieldPropertyDefault: "firstName",

    };

    // Load defaults
    for (let name in defaults)
    {
        if (!(name in options))
            options[name] = defaults[name];
    }

    loadTraits(editor, options);

    loadTypes(editor, options);


    // Add components
    //loadComponents(editor, options);

    // Add components
    loadBlocks(editor, options);

    /*
    editor.RichTextEditor.add("dynamic-field", {
        icon: `<select class="gjs-field">
		        <option value="">Select</option>
                <option value="firstName">firstName</option>
                <option value="lastName">lastName</option>
                
            </select>
        `,

        // Bind the 'result' on 'change' listener
        event: 'change',
        result: (rte, action) => rte.insertHTML("{% " + action.btn.firstChild.value + " %}"),
        // Reset the select on change
        update: (rte, action) => { action.btn.firstChild.value = "";}
    });
    */

});
