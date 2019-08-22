<template>

    <div class="vue-query-builder">

        <query-builder-group
            :index="0"
            :length="0"
            :query.sync="query"
            :ruleTypes="ruleTypes"
            :rules="mergedRules"
            :maxDepth="maxDepth"
            :depth="depth"
            :styled="styled"
            :labels="mergedLabels"
            :table="table"
            :columns="columns"
            type="query-builder-group"
        >
        </query-builder-group>

    </div>

</template>

<script>
    import QueryBuilderGroup from './components/QueryBuilderGroup.vue';
    import { deepClone } from './utilities.js';

    import VueJsonPretty from "vue-json-pretty";

    let defaultLabels = {

        matchType: "",

        matchTypes: [

            {
                id: "AND",
                label: "AND"
            },
            {
                id: "OR",
                label: "OR"
            },

        ],

        addRule: {
            text: "Rule",
            icon: "fas fa-plus",
        },
        removeRule: {
            text: "Delete",
            icon: "fas fa-times",
        },
        addGroup: {
            text: "Group",
            icon: "fas fa-plus-circle",
        },
        removeGroup: {
            text: "Delete",
            icon: "fas fa-times-circle",
        },
        textInputPlaceholder: "value",

        notifications: {
            sqlError: {
                text: "Missing values in this rule are preventing the generation of valid syntax for a SQL condition.",
                icon: "fas fa-exclamation-circle text-danger",
            },
            sqlSuccess: {
                text: "A valid SQL condition has been generated, using these values.",
                icon: "fas fa-check-circle text-success",
            }

        }

    };

    export default {
        name: 'QueryBuilder',

        components: {
            QueryBuilderGroup,
            VueJsonPretty,
        },

        props: {
            rules: Array,
            labels: {
                type: Object,
                default () {
                    return defaultLabels;
                }
            },
            styled: {
                type: Boolean,
                default: true
            },
            maxDepth: {
                type: Number,
                default: 3,
                validator: function (value) {
                    return value >= 1
                }
            },
            value: Object,
            table: {
                type: String,
                required: true,
                /*
                validator: function(value)
                {
                    return value !== "";
                }
                */
            },
            columns: Object,
        },

        data () {
            return {
                depth: 1,

                query: {
                    table: this.table,
                    logicalOperator: this.labels.matchTypes[0].id,
                    children: [],
                    sql: "",
                },

                ruleTypes: {
                    "text": {
                        operators: ['equals','does not equal','contains','does not contain','is empty','is not empty','begins with','ends with'],
                        inputType: "text",
                        id: "text-field"
                    },
                    "numeric": {
                        operators: ['=','<>','<','<=','>','>='],
                        inputType: "number",
                        id: "number-field"
                    },
                    "custom": {
                        operators: [],
                        inputType: "text",
                        id: "custom-field"
                    },
                    "radio": {
                        operators: [],
                        choices: [],
                        inputType: "radio",
                        id: "radio-field"
                    },
                    "checkbox": {
                        operators: [],
                        choices: [],
                        inputType: "checkbox",
                        id: "checkbox-field"
                    },
                    "select": {
                        operators: [],
                        choices: [],
                        inputType: "select",
                        id: "select-field"
                    },
                    "multi-select": {
                        operators: ['='],
                        choices: [],
                        inputType: "select",
                        id: "multi-select-field"
                    },
                }
            }
        },

        computed: {
            mergedLabels () {
                return Object.assign({}, defaultLabels, this.labels);
            },

            mergedRules () {
                var mergedRules = [];
                var vm = this;

                vm.rules.forEach(function(rule){
                    if ( typeof vm.ruleTypes[rule.type] !== "undefined" ) {
                        mergedRules.push( Object.assign({}, vm.ruleTypes[rule.type], rule) );
                    } else {
                        mergedRules.push( rule );
                    }
                });

                return mergedRules;
            }
        },

        mounted: function()
        {
            this.$watch(
                'query',
                newQuery => {
                    if (JSON.stringify(newQuery) !== JSON.stringify(this.value)) {
                        this.$emit('input', deepClone(newQuery));
                    }
                }, {
                    deep: true
                });

            this.$watch(
                'value',
                newValue => {
                    if (JSON.stringify(newValue) !== JSON.stringify(this.query)) {
                        this.query = deepClone(newValue);
                    }
                }, {
                    deep: true
                });

            if ( typeof this.$options.propsData.value !== "undefined" ) {
                this.query = Object.assign(this.query, this.$options.propsData.value);
            }

            this.$watch(
                'table',
                newValue => {
                    if (newValue !== this.query.table)
                    {
                        let updatedQuery = deepClone(this.query);
                        updatedQuery.table = newValue;
                        this.$emit('input', updatedQuery);
                    }
                }, {
                    deep: false
                });



        }
    }
</script>

<style>



</style>
