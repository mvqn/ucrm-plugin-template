import { styleObjectToString } from "../styles";
import { flatten, resolve } from "../helpers";

// noinspection JSUnusedLocalSymbols
/**
 * Export the plugin's "types" function.
 * @param {Object} editor
 * @param {Object} options
 */
export default function(editor, options = {})
{
    let fieldObjects = flatten(options.data);
    console.log(resolve("admin.firstName", options.data));





    // Get a quick reference to the default Component, for later use.
    const defaultType = editor.DomComponents.getType("default");

    // Add our new type.
    editor.DomComponents.addType("dynamic-field",
    {
        /**
         * Used in determining whether or not the given component is handled by our type.
         * @param value
         * @returns {*}
         */
        isType: function(value)
        {
            if(value && value.type === "dynamic-field")
                return value;

        },  // isType()

        // Setup the component's model.
        model:
        {
            // Specifically, the model defaults.
            defaults:
            {
                // This component is allowed to be dropped inside of a text component.
                textable: true,

                // But can not have anything else dropped inside of itself.
                droppable: false,

                // Set some default styles.
                style:
                {
                    display: "inline-block",

                },  // styles

                // Define the component's traits.
                traits:
                [
                    {   // The object from which to get our data.
                        type: "select",
                        label: "Object",
                        name: "field-object",
                        options: Object.keys(fieldObjects),

                        /*
                        options: [
                            "admin",
                            "user",
                            "ticket",
                            "job",
                        ],
                        */
                    },

                    {   // The property on that object from which to get our data.
                        type: "select-group",
                        label: "Property",
                        name: "field-property",
                        options: [
                            { group: "Names", id: "firstName", name: "First Name" },
                            { group: "Names", id: "middleName", name: "Middle Name" },
                            { group: "Names", id: "lastName", name: "Last Name" },
                            { group: "Addresses", id: "street1", name: "Street1" },
                            { group: "Addresses", id: "street2", name: "Street2" },
                        ],
                    },

                    {   // An optional default value to display if the above is null, defaults to empty.
                        type: "text",
                        label: "Default",
                        name: "field-default",
                        placeholder: "Value if null..."
                    },

                    {   // An optional condition for determining the visibility of the field, defaults to empty.
                        type: "text",
                        label: "Condition",
                        name: "field-condition",
                        placeholder: "Condition for rendering..."
                    },


                ],  // traits

                // Set default values for the same traits.
                attributes:
                {
                    "field-object": "user",
                    "field-property": "street1",
                    "field-default": "",
                    "field-condition": "",

                },  // attributes

            },  // defaults


            /**
             * Renders our component as HTML.
             * @returns {string}
             */
            toHTML: function()
            {
                // Get the component's attributes and styles.
                const attributes = this.getAttributes();
                const styles = styleObjectToString(this.getStyle());

                const content =
                    `${attributes["field-object"]}.${attributes["field-property"]}` + (
                    attributes["field-default"] === ""
                        ? ""
                        : `|default('${attributes["field-default"]}')`
                );

                const conditionOpen = (
                    attributes["field-condition"] === ""
                        ? ""
                        : `{% if ${attributes["field-condition"]} %}`
                );
                const conditionClose = (
                    attributes["field-condition"] === ""
                        ? ""
                        : `{% endif %}`
                );

                // And return the combination in our desired HTML format!
                return `
                    ${conditionOpen}
                    <div style="${styles}">
                        {{ ${content} }}
                    </div>
                    ${conditionClose}
                `;

            },  // toHTML()

        },  // model

        // The view differs from the actual HTML content, so we need render it manually...
        view:
        {
            // The visual element should be surrounded with a <div>.
            tagName: "div",

            /**
             * Initialize the component's view.
             */
            init: function()
            {
                this.listenTo(this.model, "change:attributes", () => { this.render(); });
                this.listenTo(this.model, "change:style", () => { this.render(); });

            },  // init()

            /**
             * Render the component's view.
             * @returns {view}
             */
            render: function ()
            {
                // Execute the default render() function...
                defaultType.view.prototype.render.apply(this, arguments);

                // Get the component's attributes.
                const attributes = this.model.getAttributes();

                // Then specifically the ones we need for displaying information to the user.
                const fieldObject = attributes["field-object"];
                const fieldProperty = attributes["field-property"];

                // Set the visual content in the editor only.
                this.$el.html(`${fieldObject}.${fieldProperty}`);

                // And add any editor only styles.
                this.$el.css("background-color", "lightblue");
                //$el.css("border-radius", "0.25rem");

                // Finally, we need to return the view!
                return this;

            },  // render()

        }   // view

    });


    editor.on("component:selected", function()
    {
        const selected = editor.getSelected();

        if(selected.attributes.type === "dynamic-field")
        {
            const openSmBtn = editor.Panels.getButton('views', 'open-tm');
            openSmBtn.set('active', 1);
        }

    });




}
