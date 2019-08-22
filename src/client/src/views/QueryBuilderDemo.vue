<!--suppress JSUnusedLocalSymbols, JSUnresolvedVariable -->
<template>
    <div>
        <div class="card mb-0">
            <div class="card-header d-flex flex-column flex-sm-row justify-content-between p-2 p-sm-3 p-md-4">

                <div class="d-flex align-items-center ">
                    <h5 class="mr-sm-3 mb-sm-0">Query Builder</h5>
                </div>

                <div class="d-flex flex-grow-1 align-items-center">

                    <div v-if="tablesLoading"  class="inline-loader">
                        <i class="fas fa-spinner fa-spin"></i>
                        Loading Tables...
                    </div>

                    <label for="table-select" class="sr-only">Table</label>

                    <select
                        id="table-select"
                        v-model="selectedTableName"
                        class="form-control"
                        :disabled="tablesLoading || (!isEmptyQuery && !isMinimalQuery)"
                    >
                        <option v-if="!tablesLoading" value="" hidden>Select a table...</option>
                        <option
                            v-for="(table, index) in tableNames"
                            :key="index"
                            :value="table"
                        >
                            {{ table }}
                        </option>

                    </select>
                </div>

            </div>

            <div class="card-body p-0 p-sm-2 p-md-3">

                <QueryBuilder
                    v-model="query"
                    :rules="rules"
                    :maxDepth="3"
                    :styled="styled"
                    :table="selectedTableName"
                    :columns="selectedTable"
                ></QueryBuilder>

            </div>

            <div class="card-footer d-flex justify-content-end p-2 p-sm-3 p-md-4">
                <button
                    type="button"
                    class="btn btn-sm btn-primary"
                    @click="executeQuery"
                    :disabled="!query.sql"
                    >Execute</button>

                <!--
                <button
                    type="button"
                    class="btn btn-sm btn-secondary"
                    @click="executeQuery($event, 'SELECT * FROM' + ' ' + query.table + ';')"
                    :disabled="!query.table"
                >Select All</button>
                -->

                <!--
                <VueJsonPretty :data="query"></VueJsonPretty>
                -->
                <!--
                <VueJsonPretty v-if="results !== null" :data="results"></VueJsonPretty>
                -->




            </div>


        </div>
        <!--<div id="jsGrid"></div>-->
        <div
            v-if="sql"
            class="mt-3"
            v-html="sql">
        </div>
    </div>
</template>

<script>

    import QueryBuilder from "../components/QueryBuilder/QueryBuilder";
    import VueJsonPretty from "vue-json-pretty";

    export default {

        name: "QueryBuilderDemo",

        components: {
            QueryBuilder,
            VueJsonPretty,
        },

        data () {
            return {

                sql: "",

                query: {},

                rules: [
                    {
                        type: "text",
                        id: "vegetable",
                        label: "Vegetable",
                    },
                    {
                        type: "radio",
                        id: "fruit",
                        label: "Fruit",
                        choices: [
                            {label: "Apple", value: "apple"},
                            {label: "Banana", value: "banana"}
                        ]
                    },
                ],

                styled: true,

                tables: {},

                results: null,


                selectedTableName: "",
                selectedTable: null,

                tablesLoading: false,
                request: null
            }
        },

        watch: {

            tables: function(current, previous)
            {
                //console.log(current);


            },

            selectedTableName: function(current, previous)
            {
                //console.log("Selected Table: " + current);

                if(current === undefined || current === null || current === "")
                    this.selectedTable = null;

                this.selectedTable = this.tables[this.selectedTableName];
                //console.log(this.selectedTable);
            },

            results: function()
            {
                console.log("Received!");

            },


        },

        computed: {



            tableNames: function()
            {

                return this.tables ? Object.keys(this.tables) : [];


            },

            isEmptyQuery: function()
            {
                for(let key in this.query) {
                    if(this.query.hasOwnProperty(key))
                        return false;
                }
                return true;
            },

            isMinimalQuery: function()
            {
                if(this.isEmptyQuery)
                    return true;

                let allowedKeys = [ "table", "logicalOperator", "children", "sql" ];

                for(let key in this.query)
                {
                    if(this.query.hasOwnProperty(key) && !allowedKeys.includes(key))
                        return false;
                }

                return !(this.query.hasOwnProperty("children") && this.query.children.length > 0);



            },


        },


        methods: {

            executeQuery: function(event, sql = null)
            {
                if (!this.query.sql && sql === null)
                    return;

                let query = sql !== null ? sql : this.query.sql;

                let self = this;

                //this.sql = query;


                this.request = $.ajax({
                    url: "public.php?/api/psql/format",
                    method: "POST",
                    data: query,
                    dataType: "text",
                    contentType: "text/html",
                    // Handle successful data acquisition...
                    success: function(data)
                    {
                        // Indicate loading and the request are complete.
                        //self.tablesLoading = false;
                        self.request = null;

                        //self.results = JSON.parse(data);
                        //console.log(data);
                        self.sql = data;
                    },

                    // Handle unsuccessful data acquisition...
                    error: function(error)
                    {
                        // IF the operation was aborted, THEN ignore, OTHERWISE log the error to the browser console!
                        if(error.statusText !== "abort")
                            console.log(error);
                    }
                });



                /*
                this.request = $.ajax({
                    url: "public.php?/api/psql/query",
                    method: "POST",
                    data: query,
                    dataType: "text",
                    contentType: "application/json",
                    // Handle successful data acquisition...
                    success: function(data)
                    {
                        // Indicate loading and the request are complete.
                        //self.tablesLoading = false;
                        self.request = null;

                        //self.results = JSON.parse(data);

                        let fields = [];

                        Object.keys(self.selectedTable).forEach(function(columnName, index)
                        {
                            let column = self.selectedTable[columnName];
                            let type = "text";

                            //switch(column.type)
                            //{
                            //}

                            fields.push({
                                name: column.name,
                                type: type,
                                width: "auto",
                            });


                        });

                        $("#jsGrid").jsGrid({
                            width: "100%",
                            height: "400px",

                            inserting: false,
                            editing: false,
                            sorting: true,
                            paging: true,

                            data: JSON.parse(data),



                            fields: fields,

                            onRefreshed: function(grid)
                            {
                                console.log(grid);
                            },

                        });



                    },

                    // Handle unsuccessful data acquisition...
                    error: function(error)
                    {
                        // IF the operation was aborted, THEN ignore, OTHERWISE log the error to the browser console!
                        if(error.statusText !== "abort")
                            console.log(error);
                    }
                });
                */


            }


        },

        created() {
            console.log("QueryBuilderDemo");
        },

        mounted: function()
        {
            let self = this;

            this.tablesLoading = true;

            this.request = $.ajax({
                url: "public.php?/api/psql/schemas",
                method: "GET",

                // Handle successful data acquisition...
                success: function(data)
                {
                    //let tables = {};
                    let tables = [];

                    // Loop through each row in the resulting dataset...
                    data.forEach(
                        function(column)
                        {

                            let table = column["table_name"];
                            let name = column["column_name"];
                            let type = column["udt_name"];
                            let nullable = column["is_nullable"];

                            if(!tables.hasOwnProperty(table))
                                tables[table] = {};

                            if(!tables[table].hasOwnProperty(name))
                                tables[table][name] = {};

                            tables[table][name]["name"] = name;
                            tables[table][name]["type"] = type;
                            tables[table][name]["nullable"] = nullable;


                        }
                    );

                    self.tables = tables;

                    // Indicate loading and the request are complete.
                    self.tablesLoading = false;
                    self.request = null;

                    //console.log(self.tables);
                },

                // Handle unsuccessful data acquisition...
                error: function(error)
                {
                    // IF the operation was aborted, THEN ignore, OTHERWISE log the error to the browser console!
                    if(error.statusText !== "abort")
                        console.log(error);
                }
            });


        }

    }

</script>

<!-- Add "scoped" attribute to limit CSS to this component only -->
<style>

@import "../assets/css/query-builder.css";


</style>
