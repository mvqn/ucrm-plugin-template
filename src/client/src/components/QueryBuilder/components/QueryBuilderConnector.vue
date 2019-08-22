<template>
    <div :class="'vqb-connector-' + type">
        <div class="vqb-connector-region-top">
            <div class="vqb-connector-region-tl"></div>
            <div class="vqb-connector-region-tr"></div>
        </div>
        <div class="vqb-connector-region-bottom">
            <div class="vqb-connector-region-bl"></div>
            <div class="vqb-connector-region-br"></div>
        </div>
    </div>
</template>

<script>

    const BORDER_DEFAULT = "3px solid gray";

    export default {

        name: "QueryBuilderConnector",

        props: {

            type: {
                type: String,
                required: true,
                validator: function(value)
                {
                    return ["straight", "single", "double"].includes(value);
                },
            },

            border: {
                type: String,
                required: false,
                default: BORDER_DEFAULT,
            },



        },

        watch: {

            type: function(current, previous)
            {
                //this.$emit("input", current);
            },

            border: function(current, previous)
            {
                this.changeBorderCSS(current);

            }

        },

        data: function()
        {
            return {

                //html: null,

            };
        },

        methods: {

            changeBorderCSS: function(css)
            {
                if(css !== BORDER_DEFAULT)
                {
                    $(".vqb-connector-region-tr")
                        .css("border-left", css);

                    $(".vqb-connector-straight .vqb-connector-region-br")
                        .css("border-left", css);

                    $(".vqb-connector-single .vqb-connector-region-tr")
                        .css("border-bottom", css);

                    $(".vqb-connector-double .vqb-connector-region-tr")
                        .css("border-left", css)
                        .css("border-bottom", css);

                    $(".vqb-connector-double .vqb-connector-region-br")
                        .css("border-left", css);

                    this.$emit("borderChange", css);
                }

            }



        },



        mounted: function()
        {
            this.changeBorderCSS(this.border);

            //console.log(this.type);



        }
    }
</script>

<style>




    .vqb-connector-straight, .vqb-connector-single, .vqb-connector-double
    {
        min-width: 1.5rem;
        max-width: 1.5rem;
        min-height: 0.75rem;
    }

    .vqb-connector-region-top
    {
        width: 100%;
        height: calc(50% + 1.5px);
    }

    .vqb-connector-region-bottom
    {
        width: 100%;
        height: calc(50% - 1.5px);
    }

    .vqb-connector-region-tl, .vqb-connector-region-tr, .vqb-connector-region-bl, .vqb-connector-region-br
        /* div[class^="vqb-connector-region-"][class$="-tl"],[class$="-tr"],[class$="-bl"],[class$="-br"] */
    {
        width: 50%;
        height: 100%;
        float:left;
        border: none;
    }

    .vqb-connector-region-tr
    {
        border-left: 3px solid gray;
    }

    .vqb-connector-straight .vqb-connector-region-br
    {
        border-left: 3px solid gray;
    }

    .vqb-connector-single .vqb-connector-region-tr
    {
        border-bottom: 3px solid gray;
        border-bottom-left-radius: 0.5rem;
    }

    .vqb-connector-double .vqb-connector-region-tr
    {
        border-left: 3px solid gray;
        border-bottom: 3px solid gray;
        bottom: 0;
    }

    .vqb-connector-double .vqb-connector-region-br
    {
        border-left: 3px solid gray;
        bottom: 0;
    }
</style>
