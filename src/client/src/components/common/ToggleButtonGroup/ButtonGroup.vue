<template>
    <div ref="buttonGroupContainer" :id="'button-container-' + _uid" class="d-flex w-100">
        <div ref="buttonGroup" class="btn-group btn-group-toggle">
            <label
                ref="buttons"
                v-for="(button, index) in localButtons"
                v-if="!hiddenHandler(button.hidden, button.hiddenArgs)"
                :key="'button-' + index"
                :class="[
                    { 'active': toggle && button.value === value },
                    buttonClass,
                    button.class,
                    { 'disabled': hiddenHandler(button.disabled, button.disabledArgs) },
                ]"
                class="btn btn-sm btn-primary"
            >
                <input
                    :key="'input-' + index"
                    type="radio"
                    name="matchTypes"
                    class="tbg-input"
                    autocomplete="off"
                    :value="button.value"
                    v-model="model"
                    @click="$emit('click', $event.target.value)"
                    :checked="toggle && button.value === value"
                    :disabled="hiddenHandler(button.disabled, button.disabledArgs)"
                >
                <span ref="buttonIcons" class="button-icon">
                    <i v-if="button.showIcon" :class="button.icon"></i>
                </span>
                <!-- :class="{ 'd-none d-sm-inline': button.icon }" -->
                <span ref="buttonLabels" class="button-text">
                    {{ button.showLabel ? button.label: "" }}
                </span>
            </label>
        </div>
    </div>
</template>

<script>

    export default {

        name: "ButtonGroup",

        props: {
            value: {
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

            /**
             * NOTE: The absolute minimum data that can be passed to the component is an array of objects containing at
             * least the "value" property with a valid value.
             */
            buttons: {
                type: Array,
                required: true,
                validator: function(objects)
                {
                    if(objects === undefined || objects === null || objects.length === 0)
                        return false;

                    let valid = true;

                    objects.forEach(
                        function(object)
                        {
                            if (object === undefined || object === null || typeof object !== "object" ||
                                object === {} || !object.hasOwnProperty("value") ||
                                object.value === undefined || object.value === null || object.value === "")
                                return valid = false;
                        }
                    );

                    return valid;
                }
            },

            autoSize: {
                type: Boolean,
                default: false,
            },

            /*
            useBootstrap4: {
                type: Boolean,
                default: true,
            },

            useUniformSize: {
                type: Boolean,
                default: true,
            },
            */

            buttonClass: {
                type: String,
                default: "",
            },

            toggle: {
                type: Boolean,
                required: false,
                default: false,
            },

        },


        watch: {

            model: function(current, previous)
            {
                if(this.toggle)
                    this.$emit("input", current);
            },

            buttons: function(current, previous)
            {
                this.localButtons = this.fixButtons(current);
            },

            localButtons: function(current, previous)
            {
                //console.log("changed!");

                this.$emit("buttonsChanged", current);
            },


        },

        methods: {

            //#region Mutations

            fixButtons: function(current)
            {
                let modified = [];

                current.forEach(function(button, index)
                {
                    let temp = button;

                    if(!temp.hasOwnProperty("label"))
                        temp["label"] = "";

                    if(!temp.hasOwnProperty("icon"))
                        temp["icon"] = "";

                    if(!temp.hasOwnProperty("class"))
                        temp["class"] = "";

                    if(!temp.hasOwnProperty("disabled"))
                        temp["disabled"] = false;

                    if(!temp.hasOwnProperty("disabledArgs"))
                        temp["disabledArgs"] = {};

                    if(!temp.hasOwnProperty("hidden"))
                        temp["hidden"] = false;

                    if(!temp.hasOwnProperty("hiddenArgs"))
                        temp["hiddenArgs"] = {};

                    if(!temp.hasOwnProperty("showIcon"))
                        temp["showIcon"] = temp.icon !== "";

                    if(!temp.hasOwnProperty("showLabel"))
                        temp["showLabel"] = temp.label !== "";

                    modified.push(temp);
                });

                return modified;
            },

            //#endregion



            buttonClicked: function(e)
            {
                this.$emit("click", e.target.value);
            },


            hiddenHandler: function(hidden, args)
            {
                if(hidden === undefined || hidden === null)
                    return false;

                //console.log(hidden);

                switch(typeof hidden)
                {
                    case "boolean":
                        //console.log(hidden);
                        return hidden;
                    case "function":
                        //console.log(this);
                        //console.log(hidden(args));
                        return hidden(args);
                    default:
                        return true;
                }

            },

            disabledHandler: function(disabled, args)
            {
                if(disabled === undefined || disabled === null)
                    return false;

                //console.log(hidden);

                switch(typeof disabled)
                {
                    case "boolean":
                        //console.log(hidden);
                        return disabled;
                    case "function":
                        //console.log(this);
                        //console.log(hidden(args));
                        return disabled(args);
                    default:
                        return true;
                }

            },


            //#region Sanity Checks (Icons)

            areUsingAnyIcons: function()
            {
                let any = false;

                this.localButtons.forEach(function(button, index)
                {
                    if (button.icon !== "" && button.showIcon)
                        any = true;
                });

                return any;
            },

            areUsingAllIcons: function()
            {
                let all = true;

                this.localButtons.forEach(function(button, index)
                {
                    if (button.icon === "" || !button.showIcon)
                        all = false;
                });

                return all;
            },

            //#endregion

            //#region Sanity Checks (Labels)

            areUsingAnyLabels: function()
            {
                let any = false;

                this.localButtons.forEach(function(button, index)
                {
                    if (button.label !== "" && button.showLabel)
                        any = true;
                });

                return any;
            },

            areUsingAllLabels: function()
            {
                let all = true;

                this.localButtons.forEach(function(button, index)
                {
                    if (button.label === "" || !button.showLabel)
                        all = false;
                });

                return all;
            },

            //#endregion

            //#region Visibility (Icons)

            hideAllIcons: function(force = false)
            {
                this.localButtons.forEach(function(button, index)
                {
                    // NOTE: We do not want the button to be completely empty, so here we make sure there is an icon
                    // set and it is flagged to be shown before disabling the label.  It can be forced!
                    if(force || (button.label !== "" && button.showLabel))
                        button.showIcon = false;
                });

                this.$forceUpdate();
            },

            showAllIcons: function()
            {
                this.localButtons.forEach(function(button, index)
                {
                    button.showIcon = true;
                });

                this.$forceUpdate();
            },

            //#endregion

            //#region Visibility (Labels)

            hideAllLabels: function(force = false)
            {
                let self = this;

                this.localButtons.forEach(function(button, index)
                {
                    // NOTE: We do not want the button to be completely empty, so here we make sure there is an icon
                    // set and it is flagged to be shown before disabling the label.  It can be forced!
                    if(force || (button.icon !== "" && button.showIcon))
                    {
                        button.showLabel = false;
                    }
                });

                this.$forceUpdate();
            },

            showAllLabels: function()
            {
                this.localButtons.forEach(function(button, index)
                {
                    button.showLabel = true;
                });

                this.$forceUpdate();
            },

            //#endregion



            getBootstrapBreakpoint: function()
            {
                let windowWidth = $(window).width();

                let breakpoint = "xs";

                if(windowWidth >= 576)
                    breakpoint = "sm";
                if(windowWidth >= 768)
                    breakpoint = "md";
                if(windowWidth >= 992)
                    breakpoint = "lg";
                if(windowWidth >= 1200)
                    breakpoint = "xl";

                return breakpoint;
            },



        },

        data: function()
        {
            return {

                model: null,


                localButtons: [
                    // NOTE: We include an example here, so that the Vue Manager can generate the proper getters and
                    // setters for later reactivity.  This item will be overwritten immediately after the component is
                    // mounted!
                    {
                        value: "delete",
                        label: "Delete",
                        icon: "fas fa-times",
                        class: "btn-danger",
                        disabled: false,
                        hidden: false,
                        showIcon: true,
                        showLabel: true,
                    }

                ],

                minimized: false,
                buttonWidth: 0,
            }
        },

        created: function()
        {

        },

        mounted: function()
        {
            let self = this;

            this.localButtons = this.fixButtons(this.buttons);



            let $container = $(self.$refs.buttonGroupContainer);
            let $group = $(self.$refs.buttonGroup);

            $container.resize(
                function()
                {
                    if(self.getBootstrapBreakpoint() === "xs" && self.autoSize)
                    {
                        let containerWidth = $container.width();
                        let groupWidth = $group.width();


                        if(groupWidth >= (containerWidth - 6) && !self.minimized)
                        {
                            self.buttonWidth = groupWidth;
                            console.log("hiding...");
                            self.hideAllLabels();
                            self.minimized = true;

                        }

                        if(self.buttonWidth > 0 && self.buttonWidth < (containerWidth - 6) && self.minimized)
                        {
                            console.log("showing...");
                            self.showAllLabels();
                            self.minimized = false;
                        }





                    }




                }
            );






        }



    }
</script>

<style scoped>



</style>
