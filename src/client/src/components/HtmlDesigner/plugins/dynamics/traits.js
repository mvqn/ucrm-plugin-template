import grapesjs from "grapesjs";

import "./helpers";

/**
 *
 * @param {object} editor
 * @param options
 */
export default function(editor, options = {})
{
    editor.TraitManager.addType('select-group', {

        // Expects as return a simple HTML string or an HTML element
        createInput: function({ trait })
        {
            // Here we can decide to use properties from the trait
            const traitOpts = trait.get('options') || [];

            let groups = traitOpts.groupBy("group");
            let optionGroups = "";

            Object.keys(groups).forEach(function(group)
            {
                optionGroups += `<optgroup label="${group}" style="background-color:rgba(0,0,0,0.8);">`;

                groups[group].forEach(function(option)
                {
                    optionGroups += `<option value="${option.id}">${option.name}</option>`;
                });

                optionGroups += `</optgroup>`;
            });


            // Create a new element container and add some content
            const el = document.createElement('div');

            el.innerHTML = `
                <div data-input>
                    <select class="gjs-select">
                        ${optionGroups}
                    </select>
                    <div class="gjs-sel-arrow">
                        <div class="gjs-d-s-arrow"></divclass>
                    </div>
                </div>
            `;





            return el;
        },

        // Update the component based on element changes
        // `elInput` is the result HTMLElement you get from `createInput`
        onEvent({ elInput, component, event })
        {
            component.addAttributes({ "field-property": event.target.value })
        },

        // Update elements on the component change
        onUpdate({ elInput, component }) {
            const href = component.getAttributes()["field-property"] || '';
            const select = elInput.querySelector('.gjs-select');

            select.value = href;

            select.dispatchEvent(new CustomEvent('change'));
        },


    });

    /*
    // Each new type extends the default Trait
    editor.TraitManager.addType("select-group", {

        events: {
            'keyup': 'onChange', // trigger parent onChange method on keyup
        },

        //Triggered when the value of the model is changed
        onValueChange: function()
        {
            const traitModel = this.model;
            const selectedComponent = this.target;
            const inputValue = traitModel.get('value');

            //... eg. update attributes
            //selectedComponent.set('attributes', {...});
        },

         // Returns the input element
         // @return {HTMLElement}
        getInputEl() {
            const input = document.createElement('textarea');
            // ...
            return input;
        },
    })
    */


}
