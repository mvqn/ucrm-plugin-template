<template>
    <div class="card mb-3">

        <div class="card-header d-flex justify-content-between align-items-center">

            <div class="d-flex align-items-center">
                <h5 class="mb-0">Group Permissions</h5>
            </div>

            <button
                ref="collapseButton"
                class="collapse-button btn p-0"
                data-toggle="collapse"
                :data-target="'#collapse-' + _uid"
                aria-expanded="true"
                @click="collapseClicked">
                <i
                    ref="collapseIcon"
                    class="collapse-icon fas"
                    :class="[ 'fa-chevron-' + (startExpanded ? 'down' : 'up') ]">
                </i>
            </button>

        </div>

        <div
            ref="collapse"
            :id="'collapse-' + _uid"
            class="collapse in show">

            <div class="card-body">
                <div class="d-flex flex-column flex-sm-row">

                    <div class="col-12 col-sm-5 h-100 p-0 ">
                        <div class="form-group h-100 mb-0 d-flex flex-column">
                            <div class="d-flex align-items-center">
                                <label
                                    for="available-groups"
                                    class="d-flex mb-2 justify-content-center justify-content-sm-start">
                                    Available Groups
                                </label>
                                <i
                                    class="fas fa-info-circle ml-2 mb-2"
                                    data-toggle="popover"
                                    data-trigger="hover"
                                    data-placement="bottom"
                                    data-content="The User Groups configured in the UCRM or UNMS system.">
                                </i>
                            </div>

                            <div>
                                <div
                                    v-if="availableLoading"
                                    id="available-groups-loading"
                                    class="d-flex justify-content-center align-items-center">
                                    <i class="fas fa-spinner fa-spin fa-2x"></i>
                                </div>

                                <select
                                    id="available-groups"
                                    class="form-control d-flex"
                                    multiple
                                    @change="availableSelectionChanged"
                                    @blur="blurAvailableGroups"
                                    :disabled="updating">
                                    <option
                                        v-for="(item, index) in sortedAvailable"
                                        :value="item"
                                    >
                                        {{ item }}
                                    </option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 col-sm-2 px-0 px-sm-3 my-3 my-sm-0 d-flex flex-column">
                        <div class="d-none d-sm-flex">
                            <label
                                class="sr-only">
                                Controls
                            </label>
                            <label
                                class="d-flex mb-2 justify-content-center">
                                &nbsp;
                            </label>
                        </div>
                        <div class="d-flex flex-grow-1 align-items-center">
                            <div id="control-buttons" class="d-flex flex-sm-column w-100">
                                <button
                                    id="allAvailableButton"
                                    class="btn btn-block btn-primary"
                                    @click="allSelectedAvailableClicked"
                                    :disabled="available.length === 0 || updating || availableLoading || allowedLoading"
                                    data-toggle="popover"
                                    data-trigger="hover"
                                    data-placement="bottom"
                                    data-content="Add all groups to the allowed list.">
                                    <i class="fas fa-angle-double-right"></i>
                                </button>
                                <button
                                    id="addAvailableButton"
                                    class="btn btn-block btn-secondary mt-0 mt-sm-2 ml-2 ml-sm-0"
                                    @click="addSelectedAvailableClicked"
                                    :disabled="selectedAvailable.length === 0 || updating || availableLoading || allowedLoading"
                                    data-toggle="popover"
                                    data-trigger="hover"
                                    data-placement="bottom"
                                    data-content="Add the selected groups to the allowed list.">
                                    <i class="fas fa-angle-right"></i>
                                </button>
                                <button
                                    id="addAllowedButton"
                                    class="btn btn-block btn-secondary mt-0 mt-sm-2 ml-2 ml-sm-0"
                                    @click="addSelectedAllowedClicked"
                                    :disabled="selectedAllowed.length === 0 || updating || availableLoading || allowedLoading"
                                    data-toggle="popover"
                                    data-trigger="hover"
                                    data-placement="bottom"
                                    data-content="Remove the selected groups from the allowed list.">
                                    <i class="fas fa-angle-left"></i>
                                </button>
                                <button
                                    id="allAllowedButton"
                                    class="btn btn-block btn-primary mt-0 mt-sm-2 ml-2 ml-sm-0"
                                    @click="allSelectedAllowedClicked"
                                    :disabled="allowed.length <= 1 || updating || availableLoading || allowedLoading"
                                    data-toggle="popover"
                                    data-trigger="hover"
                                    data-placement="bottom"
                                    data-content="Remove all groups from the allowed list.">
                                    <i class="fas fa-angle-double-left"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 col-sm-5 h-100 p-0 ">
                        <div class="form-group h-100 mb-0 d-flex flex-column">
                            <div class="d-flex align-items-center justify-content-end">
                                <i
                                    class="fas fa-info-circle mr-2 mb-2"
                                    data-toggle="popover"
                                    data-trigger="hover"
                                    data-placement="bottom"
                                    data-content="The User Groups currently allowed to access this plugin.">
                                </i>
                                <label
                                    for="allowed-groups"
                                    class="d-flex mb-2 justify-content-center justify-content-sm-end">
                                    Allowed Groups
                                </label>
                            </div>

                            <div>
                                <div
                                    v-if="allowedLoading"
                                    id="allowed-groups-loading"
                                    class="d-flex justify-content-center align-items-center">
                                    <i class="fas fa-spinner fa-spin fa-2x"></i>
                                </div>

                                <select
                                    id="allowed-groups"
                                    class="form-control d-flex"
                                    multiple
                                    @change="allowedSelectionChanged"
                                    @blur="blurAllowedGroups"
                                    :disabled="updating">
                                    <option
                                        v-for="(item, index) in sortedAllowed"
                                        :value="item"
                                        :disabled="item === 'Admin Group'"
                                    >
                                        {{ item }}
                                    </option>
                                </select>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

            <div class="card-footer d-flex justify-content-end">

                <button
                    ref="updateButton"
                    class="btn btn-primary"
                    @click="updateClicked"
                    :disabled="allowed.length < 1">
                    Update
                </button>

            </div>

        </div>

    </div>
</template>

<script>

    import api from "../../services/api";



    export default {

        name: "PermissionGroups",

        props: {

            /*
            value: {
                type: Array,
                default: function() { return [ ]; },
            },
            */

            startExpanded: {
                type: Boolean,
                default: true,
            },


        },

        watch: {

            value: function(current, previous)
            {
                this.allowed = current;
                this.$emit("input", current);
            },


            items: function(current, previous)
            {
                this.available = current;

                // Wait until the actual SELECT children are updated and then adjust the control's height.
                this.$nextTick(this.autoSelectHeight);
                this.$emit("update:items", current);
            },



            available: function(current, previous)
            {
            },

            allowed: function(current, previous)
            {
            },



            selectedAvailable: function(current, previous)
            {
                this.$nextTick(function()
                {
                    $("#available-groups").val(current);
                });
            },

            selectedAllowed: function(current, previous)
            {
                this.$nextTick(function()
                {
                    $("#allowed-groups").val(current);
                });
            },




            updating: function(current, previous)
            {

                let $button = $(this.$refs.updateButton);

                /*
                let $available = $("#available-groups");
                let $allowed = $("#allowed-groups");
                let $allAvailableButton = $("#allAvailableButton");
                let $addAvailableButton = $("#addAvailableButton");
                let $addAllowedButton = $("#addAllowedButton");
                let $allAllowedButton = $("#allAllowedButton");

                 */

                if(current)
                {
                    let width = $button.outerWidth();
                    $button.css("width", width + "px");
                    $button.html("<i class='fas fa-spinner fa-spin'></i>");
                    $button.attr("disabled", true);

                    //$available.attr("disabled", true);
                    //$allowed.attr("disabled", true);

                }
                else
                {
                    $button.html("Update");
                    $button.css("width", "");
                    $button.attr("disabled", false);

                    //$available.attr("disabled", false);
                    //$allowed.attr("disabled", false);
                }







            },



        },

        computed: {

            sortedAvailable: function()
            {
                let sorted = [];

                this.items.forEach(
                    $.proxy(
                        function(item, index, items)
                        {
                            if(this.available.includes(item))
                                sorted.push(item);
                        },
                        this
                    )
                );

                return sorted;

            },

            sortedAllowed: function()
            {
                let sorted = [];

                this.items.forEach(
                    $.proxy(
                        function(item, index, items)
                        {
                            if(this.allowed.includes(item))
                                sorted.push(item);
                        },
                        this
                    )
                );

                return sorted;
            },


        },


        data: function()
        {
            return {

                available: [],
                allowed: [],

                selectedAvailable: [],
                selectedAllowed: [],

                items: [],

                availableLoading: false,
                allowedLoading: false,


                updating: false,

            }
        },


        methods: {

            updateClicked: function()
            {
                console.log(this.allowed);

                this.updating = true;
                api.setGroupsAllowed(this.allowed)
                    .then(
                        $.proxy(
                            function(names)
                            {
                                this.updating = false;
                            },
                            this
                        )
                    );
            },



            moveToAllowed: function(groups, selected = false)
            {
                this.selectedAvailable = groups;

                this.$nextTick(
                    $.proxy(
                        function()
                        {
                            $("#addAvailableButton").click();

                            if(!selected)
                                this.selectedAllowed = [];
                        },
                        this
                    )
                );

            },

            moveToAvailable: function(groups, selected = false)
            {
                this.selectedAllowed = groups;

                this.$nextTick(
                    $.proxy(
                        function()
                        {
                            $("#addAllowedButton").click();

                            if(!selected)
                                this.selectedAvailable = [];
                        },
                        this
                    )
                );

            },


            //#region Event Handlers

            availableSelectionChanged: function()
            {
                let $available = $("#available-groups");
                this.selectedAvailable = $available.val();
            },

            allowedSelectionChanged: function()
            {
                let $allowed = $("#allowed-groups");
                this.selectedAllowed = $allowed.val();
            },


            allSelectedAvailableClicked: function()
            {
                $('[data-toggle="popover"]').popover("hide");

                this.selectedAvailable = this.available;
                this.$nextTick(function()
                {
                    $("#addAvailableButton").click();
                });
            },


            addSelectedAvailableClicked: function()
            {
                $('[data-toggle="popover"]').popover("hide");

                let available = [];
                let allowed = [];
                let selected = [];

                this.available.forEach(
                    $.proxy(
                        function(item, index, items)
                        {


                            if (this.selectedAvailable.includes(item))
                            {
                                selected.push(item);
                                allowed.push(item);
                            }
                            else
                                available.push(item);
                        },
                        this
                    )
                );

                this.available = available;
                this.allowed = this.allowed.concat(allowed);

                this.selectedAvailable = [];
                this.selectedAllowed = selected;

                //console.log(selected);

                $("#allowed-groups").focus();
            },

            getAllAllowed: function(disabled = false)
            {
                let allAllowed = [];

                this.allowed.forEach(function(group)
                {
                    if(group !== "Admin Group" || disabled)
                        allAllowed.push(group);
                });

                return allAllowed;
            },

            getAllAvailable: function(disabled = false)
            {
                let allAvailable = [];

                this.available.forEach(function(group)
                {
                    if(group !== "Admin Group" || disabled)
                        allAvailable.push(group);
                });

                return allAvailable;
            },

            allSelectedAllowedClicked: function()
            {
                $('[data-toggle="popover"]').popover("hide");

                this.moveToAvailable(this.getAllAllowed());
            },

            addSelectedAllowedClicked: function()
            {
                $('[data-toggle="popover"]').popover("hide");

                let available = [];
                let allowed = [];
                let selected = [];

                this.allowed.forEach(
                    $.proxy(
                        function(item, index, items)
                        {
                            if (this.selectedAllowed.includes(item))
                            {

                                selected.push(item);
                                available.push(item);
                            }
                            else
                                allowed.push(item);
                        },
                        this
                    )
                );

                this.available = this.available.concat(available);
                this.allowed = allowed;

                this.selectedAvailable = selected;
                this.selectedAllowed = [];

                $("#available-groups").focus();
            },


            blurAvailableGroups: function(event)
            {
                //$(event.target).val([]);

                //this.selectedAvailable = [];

            },

            blurAllowedGroups: function(event)
            {
                //$(event.target).val([]);

                //this.selectedAllowed = [];
            },



            //#endregion


            collapseClicked: function()
            {
                let $button = $(this.$refs.collapseButton);
                let $icon = $(this.$refs.collapseIcon);

                if($button.hasClass("collapsed"))
                    $icon.css("transform", this.startExpanded ? "rotate(0deg)" : "rotate(-180deg)");
                else
                    $icon.css("transform", this.startExpanded ? "rotate(180deg)" :  "rotate(0deg)");
            },


            autoSelectHeight: function(min = 176) // Center button height
            {
                let $available = $("#available-groups");
                let $allowed = $("#allowed-groups");
                let $controls = $("#control-buttons");

                let $availableLoading = $("#available-groups-loading");
                let $allowedLoading = $("#allowed-groups-loading");

                if($available.children().length === 0)
                {
                    // Default input/control height!
                    //$available.css("height", $controls.height() + "px");
                    //$allowed.css("height", $controls.height() + "px");
                    //$availableLoading.css("height", $controls.height() + "px");
                    //$allowedLoading.css("height", $controls.height() + "px");
                    $available.css("height", min + "px");
                    $allowed.css("height", min + "px");
                    $availableLoading.css("height", min + "px");
                    $allowedLoading.css("height", min + "px");
                    return;
                }

                //let extraHeight = $select.outerHeight() -  $select.height();
                let height = $available.outerHeight() -  $available.height();
                //console.log("*", height);

                $available.children().each(function(index, option, options)
                {
                    let $option = $(option);
                    height += $option.outerHeight(true);
                });

                //if(height < $controls.height())
                //    height = $controls.height();
                if(height < min)
                    height = min;

                $available.css("height", height + "px");
                $allowed.css("height", height + "px");
                $availableLoading.css("height", height + "px");
                $allowedLoading.css("height", height + "px");
                //console.log("sized");
            },

        },

        created: function()
        {
            let self = this;

            this.availableLoading = true;
            this.allowedLoading = true;

            api.getGroupsAvailable()
                .then(function(names)
                {
                    self.items = names;
                    self.availableLoading = false;

                    api.getGroupsAllowed(self.available)
                        .then(function(names)
                        {
                            self.moveToAllowed(names);
                            self.allowedLoading = false;
                        });

                });








        },

        mounted: function()
        {
            //this.available = this.items;

            //this.allowed = this.value;

            let self = this;





            //let self = this;




            this.$nextTick(
                $.proxy(
                    function()
                    {
                        this.autoSelectHeight();

                        let $button = $(this.$refs.collapseButton);
                        let $icon = $(this.$refs.collapseIcon);
                        let $collapse = $(this.$refs.collapse);

                        if(!this.startExpanded)
                        {
                            $button
                                .attr("aria-expanded", false)
                                .addClass("collapsed");

                            $collapse.removeClass("show");
                        }




                    },
                    this
                )
            );



            $(function()
            {
                $('[data-toggle="popover"]').popover();


            });


        },

    }
</script>

<style lang="stylus" scoped>

.collapse-button
    box-shadow none
    i.collapse-icon
        transition transform 0.3s ease-in-out


#available-groups-loading, #allowed-groups-loading
    position absolute
    width 100%


@media screen and (max-width: 575px)
    #allAvailableButton, #addAvailableButton, #addAllowedButton, #allAllowedButton
        & i
            transform rotate(90deg)




</style>
