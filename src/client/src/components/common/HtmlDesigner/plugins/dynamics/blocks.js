import { dynamicField } from "./consts";

export default function(editor, opt = {})
{

    // Add the "Data Field" block.
    editor.BlockManager.add("data-field",
    {
        label: "Data Field",
        category: "Dynamics",
        select: true,
        content: {
            type: "dynamic-field",
        },
        attributes: {
            class: "fa fa-database",
        }
    });

    // Add the "Data List" block.
    editor.BlockManager.add("data-list",
    {
        label: "Data List",
        category: "Dynamics",
        select: true,
        content: {
            type: "dynamic-list",
        },
        attributes: {
            class: "fa fa-list",
        }
    });

}
