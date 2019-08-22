<template>
    <div :id="id" class="tbg-button-group btn-group btn-group-toggle">
        <label
            v-for="(combined, index) in combinedValues"
            :class="[ { 'active': combined[valueName] === value }, buttonClass ]"
            class="tbg-button btn btn-sm btn-primary">
            <input
                type="radio"
                name="matchTypes"
                class="tbg-input"
                autocomplete="off"
                :value="combined[valueName]"
                v-model="model"
                :checked="combined[valueName] === value">
            <i v-if="combined.icon" :class="combined.icon"></i>
            {{ combined[labelName] }}
        </label>
    </div>
</template>

<script>

    export default {

        name: "ToggleButtonGroup",

        props: {
            value: {
            },

            id: {
                type: String,
                required: false,
                default: "toggle-button-group",
                validator: function(id)
                {
                    // Per HTML5, anything else should be a valid name for the "id" attribute.
                    return (id !== undefined && id !== null && id !== "" && !id.includes(" "));
                }
            },

            name: {
                type: String,
                required: false,
                default: "options",
                validator: function(name)
                {
                    // Per HTML5, anything else should be a valid name for the "name" attribute.
                    return (name !== undefined && name !== null && name !== "" && !name.includes(" "));
                }
            },

            values: {
                type: Array,
                required: false,
                validator: function(values)
                {
                    if(values === undefined || values === null || values.length === 0)
                        return false;

                    let valid = true;

                    values.forEach(
                        function(value)
                        {
                            if (value === undefined || value === null || typeof value !== "string")
                                valid = false;
                        }
                    );

                    return valid;
                }
            },

            labels: {
                type: Array,
                required: false,
                validator: function(labels)
                {
                    if(labels === undefined || labels === null || labels.length === 0)
                        return false;

                    let valid = true;

                    labels.forEach(
                        function(label)
                        {
                            if (label === undefined || label === null || typeof label !== "string")
                                valid = false;
                        }
                    );

                    return valid;
                }
            },

            valueName: {
                type: String,
                required: false,
                default: "value"
            },

            labelName: {
                type: String,
                required: false,
                default: "label"
            },

            combined: {
                type: Array,
                required: false,
                validator: function(objects)
                {
                    if(objects === undefined || objects === null || objects.length === 0)
                        return false;

                    let valid = true;

                    objects.forEach(
                        function(object)
                        {
                            if (object === undefined || object === null || typeof object !== "object" ||
                                object === {})
                                return valid = false;
                        }
                    );

                    return valid;
                }
            },

            useBootstrap4: {
                type: Boolean,
                default: true,
            },

            useUniformSize: {
                type: Boolean,
                default: true,
            },

            buttonClass: {
                type: String,
                default: "",
            }


        },

        computed: {

            combinedValues: function()
            {
                if(this.combined !== undefined)
                    return this.combined;

                if(this.values.length === 0 || this.labels.length === 0)
                    return [];

                if(this.values.length !== this.labels.length)
                    return [];

                let combined = [];

                this.values.forEach($.proxy(function(value, index)
                {
                    combined.push({
                        value: value,
                        label: this.labels[index]
                    });

                }, this));

                return combined;
            }


        },

        watch: {

            model: function(current, previous)
            {
                this.$emit("input", current);
            },


            breakpoint: function(current, previous)
            {
                //console.log(current);
            },

            useUniformSize: function(current, previous)
            {
                this.setUniformSize(current);


            }

        },

        methods: {



            setUniformSize: function()
            {
                console.log("test");

                let $container = $("#" + this.id);
                let $buttons = $container.find("label");



                if(this.useUniformSize)
                {
                    let containerWidth = $container.width();

                    let minButtonWidth = Infinity;
                    let maxButtonWidth = 0;

                    $buttons.each(function(index, button)
                    {
                        let $button = $(button);
                        let buttonWidth = $button.outerWidth(true);

                        if(buttonWidth < minButtonWidth)
                            minButtonWidth = buttonWidth;

                        if(buttonWidth > maxButtonWidth)
                            maxButtonWidth = buttonWidth;
                    });

                    //if(maxButtonWidth * $buttons.length <= $container.width())
                    {
                        // Will fit inside existing container...

                        $buttons.each(function(index, button)
                        {
                            let $button = $(button);
                            $button.css("width", maxButtonWidth + "px");
                        });
                    }

                    return $container;
                }
                else
                {
                    $buttons.each(function(index, button)
                    {
                        let $button = $(button);
                        $button.css("width", "");

                    });
                }
            },




            windowResized: function()
            {
                //let $container = $("#" + this.id);

                let $window = $(window);
                let windowWidth = $window.width();

                let breakpoint = "xs";

                if(windowWidth >= 576)
                    breakpoint = "sm";
                if(windowWidth >= 768)
                    breakpoint = "md";
                if(windowWidth >= 992)
                    breakpoint = "lg";
                if(windowWidth >= 1200)
                    breakpoint = "xl";

                if(this.breakpoint !== breakpoint)
                    this.breakpoint = breakpoint;



            }


        },

        data: function()
        {
            return {

                model: null,

                breakpoint: null,


            }
        },

        created: function()
        {

        },

        mounted: function()
        {

            //this.model = this.value;


            //this.setContentSized();
            //this.setUniformSize();
            //this.setContentSizedMax();


            /*
            this.$nextTick(function()
            {
                window.addEventListener("resize", this.windowResized);
            });
            */


            this.setUniformSize();


            //$(window).resize(this.windowResized).resize();
        }



    }
</script>

<style scoped>



</style>
