<template>

    <div
        class="vqb-group-container"
        :class="[ 'depth-' + depth ]">

        <!-- Straight Connector -->
        <div
            v-if="depth > 1"
            class="d-flex">
            <QueryBuilderConnector
                :type="'straight'">
            </QueryBuilderConnector>
        </div>

        <!-- Group Content -->
        <div class="d-flex flex-column">

            <!-- ROW: Connector + Group Pane -->
            <div class="d-flex">

                <!-- Single/Double Connector -->
                <div
                    v-if="depth > 1"
                    class="d-flex">
                    <QueryBuilderConnector
                        :type="index === length - 1 ? 'single' : 'double'">
                    </QueryBuilderConnector>
                </div>

                <!-- Group Pane -->
                <div class="p-2 w-100"
                    :class="{ 'border border-primary rounded': depth > 1 }">

                    <!-- ROW: Toolbars -->
                    <div class="match-type-container d-flex flex-column flex-sm-row justify-content-between">

                        <!-- Group Match Type -->
                        <div class="d-flex mb-0 order-2 order-sm-1"
                            :class="{ 'form-group': styled }">

                            <!-- Match Type Label -->
                            <label
                                v-if="labels.matchType"
                                for="vqb-match-type" class="mr-0 mr-sm-3">
                                {{ labels.matchType }}
                            </label>

                            <!-- Match Type Toggle -->
                            <div class="btn-group btn-group-toggle">

                                <label
                                    v-for="(label, index) in labels.matchTypes"
                                    class="btn btn-sm btn-primary py-0"
                                    :class="[
                                        { 'active': label.id === query.logicalOperator },
                                        { 'disabled': !columns }
                                    ]"
                                    :style="{ cursor: !columns ? 'default' : 'pointer' }"
                                    >

                                    <input
                                        :key="index"
                                        type="radio"
                                        name="matchTypes"
                                        autocomplete="off"
                                        :value="label.id"
                                        v-model="query.logicalOperator"
                                        @click=""
                                        :checked="label.id === query.logicalOperator"
                                        :disabled="!columns">

                                    <span class="">
                                        {{ label.label }}
                                    </span>

                                </label>

                            </div>

                        </div>


                        <div class="d-flex mb-2 mb-sm-0 order-1 order-sm-2 ">

                            <!-- Group Buttons -->
                            <div class="btn-group btn-group-toggle">

                            <!-- Add Rule -->
                            <button
                                type="button"
                                class="py-0"
                                :class="[ { 'btn btn-sm btn-primary': styled } ]"
                                @click="addRule"
                                :disabled="!columns">
                                <i :class="[ labels.addRule.icon, { 'mr-1': labels.addRule.icon } ]"></i>
                                {{ labels.addRule.text }}
                            </button>

                            <!-- Add Group -->
                            <button
                                type="button"
                                class="py-0"
                                :class="[ { 'btn btn-sm btn-primary': styled } ]"
                                @click="addGroup"
                                :disabled="this.depth >= this.maxDepth || !columns">
                                <i :class="[ labels.addGroup.icon, { 'mr-1': labels.addGroup.icon } ]"></i>
                                {{ labels.addGroup.text }}
                            </button>

                            <!-- Delete Group -->
                            <button
                                type="button"
                                class="py-0"
                                :class="[ { 'btn btn-sm btn-danger': styled } ]"
                                @click="remove"
                                :disabled="this.depth <= 1">
                                <i :class="[ labels.removeGroup.icon, { 'mr-1': labels.removeGroup.icon } ]"></i>
                                {{ labels.removeGroup.text }}
                            </button>

                        </div>

                        </div>

                    </div>

                    <!-- ROW: Children -->
                    <div class="vqb-group-body ">

                        <!-- <div class="children">-->

                            <!-- Child Group & Rule Components -->
                            <component
                                v-for="(child, index) in query.children"
                                :key="index"
                                :is="child.type"
                                :type="child.type"
                                :query.sync="child.query"
                                :ruleTypes="ruleTypes"
                                :rules="rules"
                                :rule="ruleById(child.query.rule)"
                                :index="index"
                                :length = query.children.length
                                :maxDepth="maxDepth"
                                :depth="depth + 1"
                                :styled="styled"
                                :labels="labels"
                                :table="table"
                                :columns="columns"
                                v-on:child-deletion-requested="removeChild"
                                v-on:child-move-requested="moveChild">

                            </component>


                        <div
                            v-if="query.children.length === 0"
                            class="">
                            <div class="alert alert-light border mb-0 mt-2 text-center">
                                Add a Rule or Group to continue...
                            </div>
                        </div>

                        <!--</div>-->


                    </div>

                    <!-- DEBUG: Index / Length -->
                    <pre
                        v-if="asSql"
                        class="text-muted d-none d-sm-inline"
                        style="font-size: smaller; font-family: monospace;" v-text="asSqlMin">
                    </pre>

                </div>


            </div>

        </div>
    </div>
</template>

<script>
    import QueryBuilderRule from './QueryBuilderRule.vue';
    import { deepClone, indexMove } from '../utilities.js';
    import ButtonGroup from "../../ToggleButtonGroup/ButtonGroup";
    import QueryBuilderConnector from "./QueryBuilderConnector";

    export default {
        name: "query-builder-group",

        components: {
            ButtonGroup,
            QueryBuilderRule,
            QueryBuilderConnector,
        },

        props: ['ruleTypes', 'type', 'query', 'rules', 'index', 'length', 'maxDepth', 'depth', 'styled', 'labels', 'table', 'columns'],

        methods: {
            ruleById (ruleId) {
                let rule = null;

                this.rules.forEach(function(value){
                    if ( value.id === ruleId ) {
                        rule = value;
                        return false;
                    }
                });

                return rule;
            },

            addRule: function()
            {
                if(this.columns === undefined || this.columns === null || this.columns === [])
                {
                    console.warn("No table selected!");

                    return;
                }

                let updated_query = deepClone(this.query);
                let child = {
                    type: 'query-builder-rule', // Component!
                    query: {
                        //rule: this.selectedRule.id,
                        //columns: this.columns,
                        //selectedOperator: this.selectedRule.operators[0],
                        //selectedOperand: typeof this.selectedRule.operands === "undefined" ? this.selectedRule.label : this.selectedRule.operands[0],
                        column: "",
                        operator: "",
                        operand1: "",
                        operand2: "",
                        sql: "",
                        //value: null
                    }
                };

                // A bit hacky, but `v-model` on `select` requires an array.
                //if (this.ruleById(child.query.rule).type === 'multi-select') {
                //    child.query.value = [];
                //}

                updated_query.children.push(child);
                this.$emit('update:query', updated_query);
            },

            addGroup: function()
            {
                let updated_query = deepClone(this.query);
                if ( this.depth < this.maxDepth ) {
                    updated_query.children.push({
                        type: 'query-builder-group',
                        query: {
                            logicalOperator: this.labels.matchTypes[0].id,
                            children: [],
                            sql: "",
                        }
                    });
                    this.$emit('update:query', updated_query);
                }
            },

            remove: function()
            {
                this.$emit('child-deletion-requested', this.index);
            },

            removeChild: function(index)
            {
                let updatedQuery = deepClone(this.query);

                updatedQuery.children.splice(index, 1);

                this.$emit('update:query', updatedQuery);
            },

            moveChild: function(index, delta)
            {
                if(delta !== -1 && delta !== 1 )
                    return;

                let updatedQuery = deepClone(this.query);

                updatedQuery.children = indexMove(updatedQuery.children, index, index + delta);

                this.$emit('update:query', updatedQuery);
            },

        },

        data () {
            return {
                selectedRule: this.rules[0]
            }
        },

        updated: function()
        {
            let updatedQuery = deepClone(this.query);
            let changed = false;

            if (updatedQuery.sql !== this.asSql)
            {   updatedQuery.sql = this.asSql;  changed = true; }

            if (changed)
            {
                //console.log("changed");
                this.$emit('update:query', updatedQuery);
            }

        },

        computed: {
            classObject: function()
            {
                let classObject = {

                };

                classObject['depth-' + this.depth.toString()] = this.styled;

                return classObject;
            },

            buttons: function()
            {
                let buttons = [];

                this.labels.matchTypes.forEach(function(label, index)
                {
                    buttons.push({
                        value: label.id,
                        label: label.label,
                    });

                });

                return buttons;
            },

            asSql: function()
            {
                if(!this.isComplete)
                    return "";

                let self = this;
                let sql = "";


                if(this.depth === 1)
                    sql +=
                        "SELECT *\n" +
                        "FROM " + this.table + "\n" +
                        "WHERE\n(\n";
                else
                    sql += " ".repeat((this.depth - 1) * 4) + "(\n";

                this.query.children.forEach(function(child, index, children)
                {
                    if(child.type === "query-builder-rule")
                        sql += " ".repeat(self.depth * 4) +  child.query.sql +
                            (index !== children.length -1
                                ? "\n" + " ".repeat(self.depth * 4) + self.query.logicalOperator
                                : ""
                            ) + "\n";

                    if(child.type === "query-builder-group")
                        sql += child.query.sql;


                });

                // SELECT *
                // FROM <table>
                // WHERE
                // ( -- QueryBuilder:Root
                //     ( -- QueryBuilder:Group
                //         ( <column> <operator> [<operand1>] [AND <operand2>] ) AND/OR -- QueryBuilder:Rule
                //         ( <column> <operator> [<operand1>] [AND <operand2>] )
                //     ) AND/OR
                //     ( <column> <operator> [<operand1>] [AND <operand2>] )
                // );

                if(this.depth === 1)
                    sql += ");";
                else
                    sql +=
                        " ".repeat((this.depth - 1) * 4) + ")" +
                        (this.index !== this.length -1
                            ? "\n" + " ".repeat((this.depth - 1) * 4) + this.query.logicalOperator
                            : "\n"
                        ) + "\n";

                return sql;
            },

            asSqlMin: function()
            {
                return this.asSql
                    .replace(/\n/g, " ") // Replace all newline characters with a space.
                    .replace(/[ ]{2,}/g, " "); // Also reduce any multiple spaces down to a single space.
            },

            isComplete: function()
            {
                if(!this.query.hasOwnProperty("children") || this.query.children.length === 0)
                    return false;

                //if(this.sql === "" || this.sql === "(  )")
                //    all = false;


                let all = true;

                this.query.children.forEach(function(child)
                {
                    if(child.query.sql === "" || child.query.sql === "(  )")
                        all = false;
                });



                return all;
            },

        }
    }
</script>

<style>

    .vqb-spacer
    {

    }


</style>
