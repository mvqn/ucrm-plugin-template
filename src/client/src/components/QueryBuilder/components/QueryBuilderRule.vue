<!--suppress JSUnusedLocalSymbols -->
<template>
    <div>
        <div class="d-flex">
            <QueryBuilderConnector :type="'straight'"></QueryBuilderConnector>
        </div>

        <div class="d-flex">

            <QueryBuilderConnector :type="index === length - 1 ? 'single' : 'double'"></QueryBuilderConnector>

            <div class="border border-info rounded p-2 w-100">

                <!-- TOOLBARS -->
                <div class="d-flex justify-content-between mb-2">

                    <!-- TOOLBAR: Up/Down -->
                    <div class="btn-group">

                        <button
                            type="button"
                            class="btn btn-sm btn-primary py-0"
                            @click="moveUp"
                            :disabled="index === 0">
                            <i class="fas fa-chevron-up"></i>
                        </button>

                        <button
                            type="button"
                            class="btn btn-sm btn-secondary py-0"
                            @click="moveDown"
                            :disabled="index === length - 1">
                            <i class="fas fa-chevron-down"></i>
                        </button>

                    </div>

                    <!-- Notifications -->
                    <div class="d-flex justify-content-start align-items-center px-2 flex-grow-1">

                        <i
                            v-if="!isComplete"
                            :class="labels.notifications.sqlError.icon"
                            data-toggle="popover"
                            :data-content="labels.notifications.sqlError.text"
                        ></i>

                        <i
                            v-if="isComplete"
                            :class="labels.notifications.sqlSuccess.icon"
                            data-toggle="popover"
                            :data-content="this.labels.notifications.sqlSuccess.text"
                        ></i>

                        <!-- DEBUG: Index / Length -->
                        <span
                            v-if="asSql"
                            class="ml-2 text-muted d-none d-sm-inline"
                            style="font-size: small;">
                            <small>
                                <!--{ index: {{ index }}, length: {{ length }} }-->
                                SQL: {{ asSql }}
                            </small>
                        </span>


                    </div>


                    <!-- TOOLBAR: SQL/Delete -->
                    <div class="btn-group">

                        <button
                            type="button"
                            class="btn btn-sm btn-danger py-0"
                            @click="remove">
                            <i :class="[ labels.removeRule.icon, { 'mr-1': labels.removeRule.icon } ]"></i>
                            {{ labels.removeRule.text }}
                        </button>

                    </div>

                </div>

                <!-- ROW: Data Entry -->
                <div class="form-row">

                    <!-- SELECT: Column -->
                    <div
                        class="form-group col-12 col-sm-7 col-md-7 mb-0"
                        :class="[
                            query.operator && selectedOperator.operands === 0 ? 'col-lg-7' : 'col-lg-4',
                        ]">

                        <label for="column-select" class="sr-only">Column</label>

                        <select
                            id="column-select"
                            class="form-control"
                            v-model="query.column">

                            <option
                                v-if="!query.column"
                                value=""
                                hidden
                                >
                                Select a column...
                            </option>

                            <option
                                v-for="(column, index) in columns"
                                :value="column.name">
                                {{ column.name + ' (' + column.type + (column.nullable ? '' : '*') + ')' }}
                            </option>

                        </select>

                    </div>

                    <!-- SELECT: Operator -->
                    <div
                        class="form-group col-12 col-sm-5 col-md-5 mb-0 mt-2 mt-sm-0"
                        :class="[
                            query.operator && selectedOperator.operands === 0 ? 'col-lg-5' : 'col-lg-3',
                        ]">

                        <label for="operator-select" class="sr-only">Operator</label>

                        <select
                            id="operator-select"
                            class="form-control"
                            v-model="query.operator"
                            :disabled="!selectedColumn">

                            <option
                                v-if="query.column && !query.operator"
                                value=""
                                hidden
                            >
                                Select an operator...
                            </option>

                            <option
                                v-for="(operator, index) in supportedOperators"
                                :value="operator.value">
                                {{ operator.label }}
                            </option>

                        </select>

                    </div>

                    <!-- INPUT: Operand1 -->
                    <div
                        v-if="query.operator === '' || selectedOperator.operands > 0"
                        class="form-group col-12 col-sm-12 col-lg-5 mb-0 mt-2 mt-lg-0"
                        :class="[
                            !query.operator || selectedOperator.operands === 1 ? 'col-md-5 offset-md-7 offset-lg-0' : 'col-md-5',
                        ]"
                    >

                        <label for="operand1-input" class="sr-only">Operand 1</label>

                        <input
                            id="operand1-input"
                            class="form-control"
                            v-model="query.operand1"
                            :disabled="query.operator === '' || selectedOperator.operands < 1">

                    </div>

                    <div
                        v-if="query.operator !== '' && selectedOperator.operands === 2"
                        class="form-group col-12 col-sm-12 col-md-2 col-lg-3 offset-lg-4 mb-0 mt-2">

                        <div class="d-flex justify-content-center justify-content-lg-end align-items-center h-100">
                        <span>AND</span>
                        </div>
                    </div>

                    <div
                        v-if="query.operator !== '' && selectedOperator.operands === 2"
                        class="form-group col-12 col-sm-12 col-md-5 col-lg-5 mb-0 mt-2">

                        <label for="operand2-input" class="sr-only">Operand 2</label>

                        <input
                            id="operand2-input"
                            class="form-control"
                            v-model="query.operand2"
                            :disabled="query.operator === '' || selectedOperator.operands < 2"
                        >

                    </div>




                </div>
            </div>

        </div>

    </div>
</template>

<script>

    import QueryBuilderConnector from "./QueryBuilderConnector";
    import { deepClone } from '../utilities.js';

    export default {
        name: "QueryBuilderRule",

        components: {
            QueryBuilderConnector,
        },

        //props: ['query', 'index', 'length', 'rule', 'styled', 'labels', 'columns'],

        props: {

            query: Object,

            index: Number,

            length: Number,

            rule: Object,

            styled: Boolean,

            labels: Object,

            columns: Object,

        },





        updated: function()
        {
            let updatedQuery = deepClone(this.query);
            let changed = false;

            if(updatedQuery.column === "")
            {
                if (updatedQuery.operator !== "")
                {   updatedQuery.operator = ""; changed = true; }

                if (updatedQuery.operand1 !== "")
                {   updatedQuery.operand1 = ""; changed = true; }

                if (updatedQuery.operand2 !== "")
                {   updatedQuery.operand2 = ""; changed = true; }
            }

            if(updatedQuery.operator === "")
            {
                if (updatedQuery.operand1 !== "")
                {   updatedQuery.operand1 = ""; changed = true; }

                if (updatedQuery.operand2 !== "")
                {   updatedQuery.operand2 = ""; changed = true; }
            }
            else
            {
                let operands = this.selectedOperator.operands;

                if (operands === 0)
                {
                    if (updatedQuery.operand1 !== "")
                    {   updatedQuery.operand1 = ""; changed = true; }

                    if (updatedQuery.operand2 !== "")
                    {   updatedQuery.operand2 = ""; changed = true; }
                }

                if (operands === 1)
                {
                    if (updatedQuery.operand2 !== "")
                    {   updatedQuery.operand2 = ""; changed = true; }
                }
            }

            if (updatedQuery.sql !== this.asSql)
            {   updatedQuery.sql = this.asSql;  changed = true; }



            if (changed)
            {
                //console.log("changed");
                this.$emit('update:query', updatedQuery);
            }


        },

        beforeMount: function()
        {
            /*
            if (this.rule.type === 'custom-component') {
                this.$options.components[this.id] = this.rule.component;
            }
            */
        },

        methods: {






            remove: function()
            {
                this.$emit('child-deletion-requested', this.index);
            },

            moveUp: function()
            {
                this.$emit('child-move-requested', this.index, -1);
            },

            moveDown: function()
            {
                this.$emit('child-move-requested', this.index, +1);
            },




            /*
            updateQuery: function(value)
            {
                console.log("query changed");
                let updatedQuery = deepClone(this.query);

                //updatedQuery.value = value;
                updatedQuery[value] = value;

                this.$emit('update:query', updatedQuery);
            },
            */



        },

        computed: {

            asSql: function()
            {
                if(!this.isComplete)
                    return "";

                let enclosed = true;
                let operator = this.selectedOperator;

                // TODO: Add reserved word check?
                let reserved = [];

                let words = [];


                switch(operator.operands)
                {
                    case 0:
                        words = [
                            this.query.column,
                            this.query.operator
                        ];
                        break;

                    case 1:
                        words = [
                            this.query.column,
                            this.query.operator,
                            this.query.operand1,
                        ];
                        break;

                    case 2:
                        words = [
                            this.query.column,
                            this.query.operator,
                            this.query.operand1,
                            operator.to,
                            this.query.operand2,
                        ];
                        break;

                    default:
                        words = [];
                        break;
                }

                if(words === [])
                    // NOTE: We should NEVER reach this, as the above isComplete() should have prevented it!
                    return "";

                return (enclosed ? "( " : "" ) + words.filter(
                    function(word)
                    {
                        return word.trim() !== "";
                    }
                ).join(" ") + (enclosed ? " )" : "" );

            },

            isComplete: function()
            {
                if(this.query.column === "")
                    return false;

                if(this.query.operator === "")
                    return false;

                let operands = this.selectedOperator.operands;

                if (operands === 1 && this.query.operand1 === "")
                    return false;

                if (operands === 2 && (this.query.operand1 === "" || this.query.operand2 === ""))
                    return false;

                return this.sql !== "";
            },


            /*
            columnNames: function()
            {
                return this.columns ? Object.keys(this.columns) : [];
            },
            */

            selectedColumn: function()
            {
                if (this.query.column === undefined || this.query.column === null || this.query.column === "")
                    return null;

                return this.columns[this.query.column];
            },

            supportedOperators: function()
            {
                if(this.selectedColumn === undefined || this.selectedColumn === null || this.selectedColumn === {})
                    return [];

                //console.log("Selected Column: " + this.selectedColumn.name);


                let operators = [];
                let type = this.selectedColumn["type"];
                let nullable = this.selectedColumn["nullable"];
                //let type = this.columns



                switch(type)
                {
                    //#region varchar, text

                    case "varchar":
                    case "text":
                        operators.push(
                            {
                                value: "=",
                                label: "equal to",
                                operands: 1,
                                surround: "\""
                            },
                            {
                                value: "<>",
                                label: "not equal to",
                                operands: 1,
                                surround: "\""
                            },
                        );
                        break;

                    //#endregion

                    //#region int2, int4, int8, float8

                    case "int2":
                    case "int4":
                    case "int8":
                    case "float8":
                        operators.push(
                            {
                                value: "<",
                                label: "less than",
                                operands: 1,
                            },
                            {
                                value: ">",
                                label: "greater than",
                                operands: 1,
                            },
                            {
                                value: "<=",
                                label: "less than or equal to",
                                operands: 1,
                            },
                            {
                                value: ">=",
                                label: "greater than or equal to",
                                operands: 1,
                            },
                            {
                                value: "=",
                                label: "equal to",
                                operands: 1,
                            },
                            {
                                value: "<>",
                                label: "not equal to",
                                operands: 1,
                            },
                            {
                                value: "BETWEEN",
                                label: "between",
                                operands: 2,
                                to: "AND",
                            },
                            {
                                value: "NOT BETWEEN",
                                label: "not between",
                                operands: 2,
                                to: "AND",
                            },
                        );
                        break;

                    //#endregion

                    //#region date, timestamp, timestamptz

                    case "date":
                    case "timestamp":
                    case "timestamptz":
                        operators.push(
                            {
                                value: "<",
                                label: "less than",
                                operands: 1,
                                //surround: "\"",
                            },
                            {
                                value: ">",
                                label: "greater than",
                                operands: 1,
                                //surround: "\"",
                            },
                            {
                                value: "<=",
                                label: "less than or equal to",
                                operands: 1,
                                //surround: "\"",
                            },
                            {
                                value: ">=",
                                label: "greater than or equal to",
                                operands: 1,
                                //surround: "\"",
                            },
                            {
                                value: "=",
                                label: "equal to",
                                operands: 1,
                                //surround: "\"",
                            },
                            {
                                value: "<>",
                                label: "not equal to",
                                operands: 1,
                                //surround: "\"",
                            },
                            {
                                value: "BETWEEN",
                                label: "between",
                                operands: 2,
                                to: "AND",
                            },
                            {
                                value: "NOT BETWEEN",
                                label: "not between",
                                operands: 2,
                                to: "AND",
                            },
                        );
                        break;

                    //#endregion

                    //#region bool

                    case "bool":
                        operators.push(
                            {
                                value: "IS TRUE",
                                label: "is true",
                                operands: 0
                            },
                            {
                                value: "IS NOT TRUE",
                                label: "is not true",
                                operands: 0
                            },
                            {
                                value: "IS FALSE",
                                label: "is false",
                                operands: 0
                            },
                            {
                                value: "IS NOT FALSE",
                                label: "is not false",
                                operands: 0
                            },
                            {
                                value: "IS UNKNOWN",
                                label: "is unknown",
                                operands: 0
                            },
                            {
                                value: "IS NOT UNKNOWN",
                                label: "is not unknown",
                                operands: 0
                            },
                        );
                        break;

                    //#endregion

                    // NOTE: Add other types as they are examined.

                    //#region DEFAULT

                    default:
                        operators.push(
                            {
                                value: "=",
                                label: "equals"
                            }
                        );
                        break;

                    //#endregion
                }

                //#region EVERYTHING

                operators.push(
                    {
                        value: "IS DISTINCT FROM",
                        label: "is distinct from ",
                        operands: 1
                    },
                    {
                        value: "IS NOT DISTINCT FROM",
                        label: "is not distinct from",
                        operands: 1
                    },
                );

                //#endregion

                //#region NULLABLE

                if(nullable)
                {
                    operators.push(
                        {
                            value: "IS NULL",
                            label: "is null",
                            operands: 0
                        },
                        {
                            value: "IS NOT NULL",
                            label: "is not null",
                            operands: 0
                        },
                    );
                }

                //#endregion

                // NOTE: Add anything else we think of!

                return operators;
            },

            selectedOperator: function()
            {
                if (this.query.operator === undefined || this.query.operator === null || this.query.operator === "")
                    return {};

                let selected = {};

                this.supportedOperators.forEach(
                    $.proxy(
                        function(operator, index)
                        {
                            if(operator.value === this.query.operator)
                                selected = operator;

                        },
                        this
                    )
                );

                //console.log(selected);

                return selected;
            }





        },

        mounted: function()
        {
            /*
            let updatedQuery = deepClone(this.query);

            // Set a default value for these types if one isn't provided already
            if (this.query.value === null)
            {
                if (this.rule.inputType === 'checkbox') {
                    updatedQuery.value = [];
                }
                if (this.rule.type === 'select') {
                    updatedQuery.value = this.rule.choices[0].value;
                }
                if (this.rule.type === 'custom-component') {
                    updatedQuery.value = null;
                    if(typeof this.rule.default !== 'undefined') {
                        updatedQuery.value = deepClone(this.rule.default)
                    }
                }

                this.$emit('update:query', updatedQuery);
            }
            */

            $(function()
            {

                let options = {

                    placement: function (context, source)
                    {
                        let width = $(window).width();

                        if(width >= 1200)
                            return "right";
                        if(width >= 992)
                            return "right";
                        if(width >= 768)
                            return "right";
                        if(width >= 576)
                            return "right";

                        // Special breakpoint to prevent tearing of the bottom popover!
                        if(width >= 420)
                            return "right";

                        return "bottom";
                    },
                    trigger: "hover",
                };


                $('[data-toggle="popover"]')
                    .popover(options);

            });

            /*
            insertionQuery(".popover").every(
                function(element)
                {
                    let $popover = $(element);

                    if($popover.attr("x-placement") !== "bottom")
                        return;

                    let offset = ($(window).width() - $popover.outerWidth(true)) / 2;

                    let transform = $popover.css("transform");

                    let scalars = transform.replace("matrix(", "").replace(")", "").split(", ");
                    let tx = scalars[4];
                    let ty = scalars[5];

                    let x = offset;
                    let y = ty;

                    let translation = "translate3d(" + x + "px, " + y + "px, 0px)";
                    $popover.css("transform", translation);

                    let dx = x - tx;
                    let dy = y;

                    let $arrow = $popover.find(".arrow");

                    let left = parseInt($arrow.css("left").replace("px", ""));

                    $arrow.css("left", (left - dx) + "px");
                }
            );
            */

        },

    }
</script>

<style>


</style>
