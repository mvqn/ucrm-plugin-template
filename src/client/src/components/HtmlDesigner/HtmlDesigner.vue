<template>

    <!-- CONTAINER -->
    <div
        :id="'html-designer-' + this._uid + '-container'"
        class="h-100">

        <!-- MOBILE -->
        <div
            class="h-100 d-flex d-sm-none justify-content-center align-items-center">

            <div
                class="w-75 alert alert-warning text-center">
                This editor is not supported on devices with a screen width of less than 576 pixels!
            </div>

        </div>

        <!-- DESIGNER -->
        <div
            :id="'html-designer-' + this._uid + '-editor'"
            class="h-100 d-none d-sm-block border border-dark"
            v-html="value">
        </div>


        <!--
        <form id="test-form" class="test-form" action="http://grapes.16mb.com/s" method="POST" style="display:none">
            <div class="putsmail-c">
                <a href="https://putsmail.com/" target="_blank">
                    <img src="./img/putsmail.png" style="opacity:0.85;" />
                </a>
                <div class="gjs-sm-property" style="font-size: 10px">
                    Test delivering offered by <a class="nl-link" href="https://litmus.com/" target="_blank">Litmus</a> with <a class="nl-link" href="https://putsmail.com/" target="_blank">Putsmail</a>
                    <span class="form-status" style="opacity: 0">
            <i class="fa fa-refresh anim-spin" aria-hidden="true"></i>
          </span>
                </div>
            </div>
            <div class="gjs-sm-property">
                <div class="gjs-field">
        	<span id="gjs-sm-input-holder">
            <input type="email" name="email" placeholder="Email" required>
          </span>
                </div>
            </div>

            <div class="gjs-sm-property">
                <div class="gjs-field">
        	<span id="gjs-sm-input-holder">
            <input type="text" name="subject" placeholder="Subject" required>
          </span>
                </div>
            </div>
            <input type="hidden" name="body">
            <button class="gjs-btn-prim gjs-btn-import" style="width: 100%">SEND</button>
        </form>
        -->


    </div>

</template>

<script>

    import grapesjs from "grapesjs";
    import "grapesjs-preset-webpage";
    import "grapesjs-preset-newsletter";

    import "./plugins/dynamics"


    export default {
        name: "HtmlDesigner",

        components: {
        },

        props: {

            value: String,

            dynamics: {
                type: Object,
                default: function() { return {}; },
            },

        },

        watch: {

            value: function(current, previous)
            {


            },

        },


        data () {
            return {

                editor: null,


            }
        },

        computed: {

        },

        created: function()
        {
            //console.log(this.dynamics);






        },

        mounted: function()
        {

            // noinspection JSUnresolvedFunction
            let editor = this.editor = grapesjs.init({

                forceClass: false,

                container: "#html-designer-" + this._uid + "-editor",
                fromElement: true,
                storageManager: { type: null },
                panels: null,

                styleManager: {},

                plugins: [
                    //'gjs-preset-webpage',
                    'gjs-preset-newsletter',
                    "dynamics",
                ],

                pluginsOpts: {

                    "dynamics": {
                        "data": this.dynamics,
                    }

                    /*
                    "gjs-preset-webpage": {
                        // options
                        blocks: [
                        ],

                        blocksBasicOpts: {
                            // Include all blocks by default!
                        },

                        navbarOpts: {
                            // Hide all navbar blocks, as they are not supported in email messages.
                            blocks: []
                        },

                        countdownOpts: {
                            // Hide all countdown blocks, as they are not supported in email messages.
                            blocks: []
                        },

                        formsOpts: {
                            // Hide all form blocks, as they are not supported in email messages.
                            blocks: []
                        },

                    }
                    */

                    /*
                    "gjs-preset-newsletter": {

                    }
                    */
                },

                /*
                blocks: [


                ]
                */





            })
            .on("load", function(editor)
            {
                // Show borders by default
                editor.Panels.getButton('options', 'sw-visibility').set('active', 1);
            });


            editor.on("component:selected", function()
            {
                const selected = editor.getSelected();

                if(selected.attributes.type === "wrapper")
                {
                    const openSmBtn = editor.Panels.getButton('views', 'open-blocks');
                    openSmBtn.set('active', 1);
                }

            });



            //#region Options Panel

            /*
            var mdlClass = 'gjs-mdl-dialog-sm';
            var testContainer = document.getElementById("test-form");
            var contentEl = testContainer.querySelector('input[name=body]');
            var md = this.editor.Modal;
            let cmdm = this.editor.Commands;
            */

            // Add the "Send Test" command...
            this.editor.Commands.add("send-test",
            {
                run: function(editor, sender)
                {
                    // TODO: Determine how we want to send the test...

                    /*
                    sender.set('active', 0);
                    var modalContent = md.getContentEl();
                    var mdlDialog = document.querySelector('.gjs-mdl-dialog');
                    var cmdGetCode = cmdm.get('gjs-get-inlined-html');
                    contentEl.value = cmdGetCode && cmdGetCode.run(editor);
                    mdlDialog.className += ' ' + mdlClass;
                    testContainer.style.display = 'block';
                    md.setTitle('Test your Newsletter');
                    md.setContent('');
                    md.setContent(testContainer);
                    md.open();
                    md.getModel().once('change:open', function() {
                        mdlDialog.className = mdlDialog.className.replace(mdlClass, '');
                        //clean status
                    })
                    */
                }
            });

            // Remove the "Toggle Images" button, as there is not enough room and this is the least useful button...
            this.editor.Panels.removeButton("options", "gjs-toggle-images");

            // Add missing buttons...
            this.editor.Panels.addButton("options",
            [
                // Add an "Undo" button...
                {
                    id: "undo",
                    className: "fa fa-undo",
                    attributes: { title: "Undo" },
                    command: "core:undo",
                },

                // Add a "Redo" button...
                {
                    id: "redo",
                    className: "fa fa-repeat",
                    attributes: { title: "Redo" },
                    command: "core:redo",
                },

                // Add a "Clear All" button...
                {
                    id: "clear-all",
                    className: "fa fa-trash icon-blank",
                    attributes: { title: "Clear All" },
                    command: {
                        run: function(editor, sender)
                        {
                            sender && sender.set("active", false);
                            if(confirm("Are you sure you want to clear the contents?"))
                            {
                                editor.DomComponents.clear();
                                setTimeout(function()
                                {
                                    localStorage.clear();
                                }, 0);
                            }
                        }
                    },
                },

                // Add a "Send Test" button...
                {
                    id: 'send-test',
                    className: 'fa fa-paper-plane',
                    command: 'send-test',
                    attributes: {
                        'title': 'Test Newsletter',
                        'data-tooltip-pos': 'bottom',
                    },
                },

            ]);

            //#endregion







            $(function()
            {



                // Set our own tooltips...

                $(".gjs-pn-buttons span.gjs-pn-btn, .gjs-blocks-c div.gjs-block")
                    //.attr("data-toggle", "tooltip")
                    .attr("data-toggle", "popover")
                    .attr("data-placement", "bottom")
                    .attr("data-trigger", "hover")
                    .each(function()
                    {
                        $(this).attr("data-content", $(this).attr("title"));
                    })
                    .attr("title", "");

                $(".gjs-pn-buttons span.gjs-pn-btn[class*='fa-desktop']")
                    .attr("data-content", "Desktop");
                $(".gjs-pn-buttons span.gjs-pn-btn[class*='fa-tablet']")
                    .attr("data-content", "Tablet");
                $(".gjs-pn-buttons span.gjs-pn-btn[class*='fa-mobile']")
                    .attr("data-content", "Mobile");

                $(".gjs-pn-buttons span.gjs-pn-btn[class*='fa-square-o']")
                    .attr("data-content", "Show Borders");
                $(".gjs-pn-buttons span.gjs-pn-btn[class*='fa-eye']")
                    .attr("data-content", "Preview");
                $(".gjs-pn-buttons span.gjs-pn-btn[class*='fa-arrows-alt']")
                    .attr("data-content", "Fullscreen");
                $(".gjs-pn-buttons span.gjs-pn-btn[class*='fa-code']")
                    .attr("data-content", "Export");
                $(".gjs-pn-buttons span.gjs-pn-btn[class*='fa-undo']")
                    .attr("data-content", "Undo");
                $(".gjs-pn-buttons span.gjs-pn-btn[class*='fa-repeat']")
                    .attr("data-content", "Redo");
                $(".gjs-pn-buttons span.gjs-pn-btn[class*='fa-download']")
                    .attr("data-content", "Import");
                $(".gjs-pn-buttons span.gjs-pn-btn[class*='fa-trash']")
                    .attr("data-content", "Clear All");

                $(".gjs-pn-buttons span.gjs-pn-btn[class*='fa-paint-brush']")
                    .attr("data-content", "Styles");
                $(".gjs-pn-buttons span.gjs-pn-btn[class*='fa-cog']")
                    .attr("data-content", "Traits");
                $(".gjs-pn-buttons span.gjs-pn-btn[class*='fa-bars']")
                    .attr("data-content", "Layers");
                $(".gjs-pn-buttons span.gjs-pn-btn[class*='fa-th-large']")
                    .attr("data-content", "Blocks");

                /*
                $(".gjs-blocks-c div.gjs-block")
                    .attr("data-toggle", "popover")
                    .attr("data-placement", "bottom")
                    .attr("data-trigger", "hover")
                    .each(function()
                    {
                        $(this).attr("data-content", $(this).attr("title"));
                    })
                    .attr("title", "");
                */





                $('[data-toggle="popover"]').popover();
                //$('[data-toggle="tooltip"]').tooltip();

            });



        }
    }
</script>


<!--suppress CssFloatPxLength, CssUnusedSymbol -->
<style lang="stylus">

    @import "~grapesjs/dist/css/grapes.min.css"

    // Hide the Selector Settings from the StyleManager!
    .gjs-clm-tags
        display none


    // -- LAYOUT, BUTTONS & PANELS -------------------------------------------------------------------------------------

    rem2px(value)
        unit(value) is "rem" ? unit(value * 16, "px") : unit(value, unit(value))

    // NOTE: Change the padding here manually, as needed!                              @screen = 576px
    editor-width        = 576px - 2px - rem2px(2rem)                                // 542px
    canvas-width        = 320px                                                     // 320px
    panels-width        = editor-width - canvas-width                               // 222px
    canvas-percentage   = unit( ( canvas-width / editor-width ) * 100, "%")         // 59.040590405904055%
    panels-percentage   = 100% - canvas-percentage                                  // 40.959409594095945%

    // Set specific sizing for supported mobile displays.
    @media screen and (min-width: 576px)
        .gjs-pn-views, .gjs-pn-views-container
            width panels-width
        .gjs-pn-options
            z-index 5
            right "calc( %s - ( 35px * 2 ) - 5px )" % panels-width
        .gjs-cv-canvas, .gjs-pn-commands
            width "calc( 100% - %s )" % panels-width
        .gjs-pn-devices-c
            z-index 6

    // Reset sizing for non-mobile displays.
    @media screen and (min-width: 768px)
        .gjs-pn-views, .gjs-pn-views-container
            width panels-width
        .gjs-pn-options
            z-index 4
            right panels-width
        .gjs-cv-canvas, .gjs-pn-commands
            width "calc( 100% - %s )" % panels-width
        .gjs-pn-devices-c
            z-index 3

    //@media screen and (min-width: 992px)
    //@media screen and (min-width: 1200px)

    // Remove the extra margin on the rightmost buttons in each group.
    .gjs-pn-buttons
        justify-content flex-end
        > span:last-child
            margin-right 0


    // -- BOOTSTRAP RESETS ---------------------------------------------------------------------------------------------

    // Override conflicting Bootstrap styles.
    label, form
        margin-bottom 0 !important


    // -- FILE UPLOADER ------------------------------------------------------------------------------------------------

    // Fixup sizing in image upload dialog!
    .gjs-am-file-uploader form
        height 325px


    // -- POPOVERS / TOOLTIPS ------------------------------------------------------------------------------------------

    popover-background = rgba(0,0,0,0.9)

    .popover
        background-color none
        .popover-body
            border-radius 0.25rem
            background-color popover-background
            color white
        .arrow
            visibility hidden

    /*
    .bs-popover-top > .arrow:after
        border-top-color popover-background
    .bs-popover-right > .arrow:after
        border-right-color popover-background
    .bs-popover-bottom > .arrow:after
        border-bottom-color popover-background
    .bs-popover-left > .arrow:after
        border-left-color popover-background
     */

</style>
