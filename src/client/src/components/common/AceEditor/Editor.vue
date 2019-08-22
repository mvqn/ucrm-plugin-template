<template>
    <div
        :class="bootstrapClasses('card')"
        class="pgsql-container">
        <!-- HEADER -->
        <div
            :class="bootstrapClasses('card-header')"
            class="pgsql-header">
            <div
                :class="bootstrapClasses('row align-items-center')"
                class="pgsql-header-row">
                <!-- TITLE -->
                <div
                    :class="bootstrapClasses('col-12 col-md-6 my-2 my-md-0')"
                    class="pgsql-title-container">
                    <h5
                        :class="bootstrapClasses('form-label text-dark mb-0')"
                        class="pgsql-title">
                        PgSQL Editor
                    </h5>
                </div>
                <!-- THEME -->
                <div
                    :class="bootstrapClasses('col-12 col-sm-6 col-md-3 mb-2 mb-sm-0')"
                    class="pgsql-theme-container">
                    <label for="themeSelector" class="sr-only">Theme</label>
                    <select
                        id="themeSelector"
                        :class="bootstrapClasses('form-control')"
                        class="pgsql-theme-selector"
                        v-model="localTheme"
                        :disabled="!themes">
                        <option
                            v-for="(theme, index) in themes"
                            :value="theme.theme">
                            {{ theme.caption }}
                        </option>
                    </select>
                </div>
                <!-- THEME -->
                <div
                    :class="bootstrapClasses('col-12 col-sm-6 col-md-3 mb-2 mb-sm-0')"
                    class="pgsql-mode-container">
                    <label for="modeSelector" class="sr-only">Mode</label>
                    <select
                        id="modeSelector"
                        :class="bootstrapClasses('form-control')"
                        class="pgsql-mode-selector"
                        v-model="localMode"
                        :disabled="!modes">
                        <option
                            v-for="(mode, index) in modes"
                            :value="mode.mode">
                            {{ mode.caption }}
                        </option>
                    </select>
                </div>
            </div>
        </div>

        <!-- EDITOR -->
        <div
            :id="id + '-container'"
            :class="[ { 'card-body': useBootstrap }, { 'col-12': useBootstrap } ]"
            :style="[ { 'padding': !useBootstrap ? 0 : 'inherit' } ]"
            class="editor-container">
            <div
                :id="id"
                class="editor">
            </div>
        </div>

        <!-- FOOTER -->
        <div
            :class="bootstrapClasses('card-footer')"
            class="pgsql-footer">
            <div
                :class="bootstrapClasses('row')"
                class="pgsql-footer-row">
                <!-- TABLES -->
                <div
                    :class="bootstrapClasses('col-12 col-sm-6 mb-2 mb-sm-1')"
                    class="pgsql-tables-container">
                    <div
                        :class="bootstrapClasses('d-flex align-items-center justify-content-between')"
                        class="pgsql-tables-title-container">
                        <label
                            for="tableSelector"
                            :class="bootstrapClasses('mb-0')"
                            class="pgsql-tables-title"
                            data-toggle="tooltip"
                            data-placement="right"
                            title= "Selecting a table here will insert it into the editor at the current position.
                                    To insert the same table additional times, use the 'insert' link.">
                            Tables
                            <i
                                v-if="tablesLoading"
                                :class="bootstrapClasses('fas fa-spinner fa-spin ml-1')"
                                class="pgsql-tables-loading-icon">
                            </i>
                            <span
                                class="pgsql-tables-loading-text"
                                v-if="tablesLoading && !useBootstrap">
                                Loading...
                            </span>
                        </label>
                        <a
                            :class="[ { 'btn': useBootstrap }, { 'p-0': useBootstrap }, { 'text-primary':
                                useBootstrap && selectedTableName },{ 'disabled': !selectedTableName } ]"
                            class="pgsql-tables-insert"
                            @click.prevent="insertTable(selectedTableName)"
                            href="#">
                            insert
                        </a>
                    </div>
                    <select
                        id="tableSelector"
                        v-model="selectedTableName"
                        :class="bootstrapClasses('form-control')"
                        class="pgsql-tables-selector"
                        :disabled="tableNames.length === 0">
                        <option
                            v-for="(table, index) in tableNames"
                            :key="index"
                            :value="table"
                            :selected="table === selectedTableName">
                            {{ table }}
                        </option>
                    </select>
                </div>
                <!-- COLUMNS -->
                <div
                    :class="bootstrapClasses('col-12 col-sm-6 mb-2 mb-sm-1')"
                    class="pgsql-columns-container">
                    <div
                        :class="bootstrapClasses('d-flex align-items-center justify-content-between')"
                        class="pgsql-columns-title-container">
                        <label
                            for="columnSelector"
                            :class="bootstrapClasses('mb-0')"
                            class="pgsql-columns-title"
                            data-toggle="tooltip"
                            data-placement="right"
                            title= "Selecting a column here will insert it into the editor at the current position.
                                    To insert the same column additional times, use the 'insert' link.">
                            Columns
                        </label>
                        <a
                            :class="[ { 'btn': useBootstrap }, { 'p-0': useBootstrap }, { 'text-primary':
                                useBootstrap && selectedColumnName },{ 'disabled': !selectedColumnName } ]"
                            class="pgsql-columns-insert"
                            @click.prevent="insertColumn(selectedColumnName)"
                            href="#">
                            insert
                        </a>
                    </div>
                    <select
                        id="columnSelector"
                        v-model="selectedColumnName"
                        :class="bootstrapClasses('form-control')"
                        class="pgsql-columns-selector"
                        :disabled="!selectedTableName || !columnNamesByTable.hasOwnProperty(selectedTableName)
                            || (columnNamesByTable.hasOwnProperty(selectedTableName) &&
                            columnNamesByTable[selectedTableName].length === 0)">
                        <option
                            v-for="(column, index) in columnNamesByTable[selectedTableName]"
                            :key="index"
                            :value="column.name"
                            :selected="column.name === selectedColumnName">
                            {{ column.name }} ({{ column.type + (column.nullable ? "" : "*") }})
                        </option>
                    </select>
                </div>
            </div>
        </div>
    </div>
</template>

<!--suppress JSUnusedLocalSymbols -->
<script>



    /**
     * TextEditor (VueJS Component)
     * @author Ryan Spaeth <rspaeth@mvqn.net>
     *
     * @model   {string}    [value]         Used for v-model two-way data-binding.
     * @prop    {string}    [mode]          Sets the Editor's mode, defaults to "ace/mode/text".
     * @prop    {string}    [theme]         Sets the Editor's theme, defaults to "ace/theme/chrome".
     * @prop    {bool}      [useBootstrap]  Defaults to true, using Bootstrap classes for the container CSS.
     *
     * @event               input           Used for v-model two-way data-binding.
     * @event               initialized     Triggered when the Editor has completed initialization.
     * @event               modeChanged     Triggered when the Editor's mode is changed, internally or externally.
     * @event               themeChanged    Triggered when the Editor's theme is changed, internally or externally.
     */
    export default {

        name: "Editor",

        //#region Properties

        props: {

            /*
             * The input side of the two-way data-binding of "v-model"!
             */
            value: {
                type: String,
                required: true,
            },

            /*
             * The ID of the component's element.
             *
             * NOTE: This is necessary, if we want to handle multiple Editors in the same DOM.  All elements with this
             * "id" will share the same Editor instance.
             */
            id: {
                type: String,
                required: false,
                default: "editor"
            },

            /*
             * The desired Editor mode.
             *
             * NOTE: This is the parser/lexer language that the Editor uses. This defaults to "ace/mode/text".
             *
             * @see {@link https://github.com/ajaxorg/ace/tree/master/lib/ace/mode} for a complete list!
             */
            mode: {
                type: String,
                required: false,
                default: function() {
                    return "ace/mode/text";
                },
                validator: function(value)
                {
                    // Validate the current value using RegEx...
                    return (/^(?:ace\/(?:mode)\/)*(\w+)$/).exec(value) !== null;
                }
            },

            /*
             * The desired Editor theme.
             * NOTE: This is the color scheme that the Editor uses. This defaults to "ace/theme/chrome".
             *
             * @see {@link https://github.com/ajaxorg/ace/tree/master/lib/ace/theme} for a complete list!
             */
            theme: {
                type: String,
                required: false,
                default: "ace/theme/chrome",
                validator: function(value) {
                    // Validate the current value using RegEx...
                    return (/^(?:ace\/(?:theme)\/)*(\w+)$/).exec(value) !== null;
                },
            },

            /**
             * A flag to indicate that we use Bootstrap classes in the wrapper.
             */
            useBootstrap: {
                type: Boolean,
                required: false,
                default: true,
            }

        },

        //#endregion

        //#region Properties (Computed)

        computed: {

            fullMode: function()
            {
                return this.getFullMode(this.mode);
            },

            fullTheme: function()
            {
                return this.getFullTheme(this.theme);
            },

            simpleMode: function()
            {
                return this.getSimpleMode(this.mode);
            },

            simpleTheme: function()
            {
                return this.getSimpleTheme(this.theme);
            },

        },

        //#endregion

        //#region Watches

        watch: {

            /*
             * Required for "v-model" two-way data-binding!
             */
            value: function(current, previous)
            {
                // IF the new content is not the same as the old content cache...
                if (this.oldContent !== current)
                {
                    // ...THEN set the Editor's content and update the old content cache.
                    this.editor.session.setValue(current, 1);
                    this.oldContent = current;
                }
            },

            /*
             * Handles changes to the mode property.
             */
            mode: function(current, previous)
            {
                this.localMode = current;
            },

            /*
             * Handles changes to the theme property.
             */
            theme: function(current, previous)
            {
                this.localTheme = current;
            },

            localMode: function(current, previous)
            {
                /*
                let regEx = (/^(?:ace\/(?:mode)\/)*(\w+)$/).exec(current);

                if(regEx !== null)
                    current = "ace/mode/" + current.toLowerCase();
                    */

                // Change the Editor's mode.
                this.editor.session.setMode(current);

                // NOTE: Here we reset the completers, as we need to remove any of the custom completers from the old
                // "mode" and there is no convenient way to simply remove previously added completers.
                this.resetCompleters();

                // NOTE: Here we perform any common resets of the mode datasets.
                this.resetPgsqlData();

                // Handle the individual mode changes, as necessary...
                switch(current)
                {
                    case "ace/mode/pgsql":
                        this.mapPgsqlSchema();
                        break;

                    case "ace/mode/twig":
                        // TODO: Map the variables?
                        break;

                    case "ace/mode/php":
                        // TODO: Map the variables into an array?

                        break;

                    // NOTE: Add any other handlers here...

                    default:
                        break;
                }

                // Set focus to the Editor.
                this.editor.focus();



                // Trigger the "modeChanged" event!
                this.$emit("modeChanged", current, this.editor);
            },

            localTheme: function(current, previous)
            {
                /*
                let regEx = (/^(?:ace\/(?:theme)\/)*(\w+)$/).exec(current);

                if(regEx !== null)
                    current = "ace/theme/" + current.toLowerCase();

                 */

                // Change the Editor's theme.
                this.editor.setTheme(current);

                //this.setBackgroundThemeContrast(current);

                // Set focus to the Editor.
                this.editor.focus();

                // Trigger the "themeChanged" event!
                this.$emit("themeChanged", current, this.editor);



            },









            selectedTableName:  function(current, previous)
            {
                this.insertTable(current);
            },

            selectedColumnName: function(current, previous)
            {
                this.insertColumn(current);
            }

        },

        //#endregion



        data: function()
        {
            return {
                editor: null,
                oldContent: "",

                // NOTE: First we override any base component data we need to...
                content: "SELECT * from client;",

                langTools: null,
                completer: null,

                // Used to maintain the state of the AJAX requests...
                tablesLoading: false,
                request: null,

                modes: null,
                themes: null,


                map: [],

                // PGSQL
                tableNames: [],
                selectedTableName: "",
                lastTableName: "",

                columnNamesByTable: {},
                column: "",
                lastColumnName: "",
                selectedColumnName: "",

                localMode: null,
                localTheme: null,


                //interval: null
            }
        },

        // =============================================================================================================
        // VUE COMPONENT METHODS
        // =============================================================================================================

        methods: {

            bootstrapClasses: function(...classes)
            {
                if(this.useBootstrap)
                {
                    let list = [];

                    classes.forEach(function(current)
                    {
                        list.push(current.includes(" ") ? current.split(" ") : current);
                    });

                    return list;
                }

                return {};
            },



            /**
             * Returns an Object with it's properties sorted alphabetically by key name.
             */
            sortObjectByKeys: function(object)
            {
                let ordered = {};

                Object.keys(object)
                    .sort()
                    .forEach(function(key)
                    {
                        ordered[key] = object[key];
                    });

                return ordered;
            },



            getFullMode: function(mode)
            {
                let regEx = (/^(?:ace\/(?:mode)\/)*(\w+)$/).exec(mode);
                return "ace/mode/" + regEx[1].toLowerCase();
            },

            getFullTheme: function(theme)
            {
                let regEx = (/^(?:ace\/(?:theme)\/)*(\w+)$/).exec(theme);
                return "ace/theme/" + regEx[1].toLowerCase();
            },



            getSimpleMode: function(mode)
            {
                let regEx = (/^(?:ace\/(?:mode)\/)*(\w+)$/).exec(mode);
                return regEx[1].toLowerCase();
            },

            getSimpleTheme: function(theme)
            {
                let regEx = (/^(?:ace\/(?:theme)\/)*(\w+)$/).exec(theme);
                return regEx[1].toLowerCase();
            },






            init: function(base)
            {



                // Reset the completers, as we need to remove any of the custom completers from the old "mode".
                //this.resetCompleters();

                // NOTE: Here we perform any common resets of the mode datasets.
                //this.resetPgsqlData();

                //this.mapPgsqlSchema();

                //this.changeMode(this.localMode);
                //this.changeTheme(this.localTheme);

            },












            insertTable: function(name)
            {
                // Set focus to the Editor.
                this.editor.focus();

                // IF the table selection is not empty...
                if(name !== "")
                {
                    // ...THEN, start by getting the Editor's cursor position, line and previous character.
                    let pos     = this.editor.getCursorPosition();
                    let line    = this.editor.session.getLine(pos.row);
                    let prev    = line.charAt(pos.column - 1);

                    // IF the cursor is NOT at the beginning of the line AND the previous character is NOT a "space"...
                    if(pos.column !== 0 && prev !== " ")
                    {
                        // ...THEN prepend a space character to help with the auto-completion.
                        name = " " + name;
                    }

                    //console.log(name);

                    // Insert the "table" name into the Editor at the current position.
                    this.editor.session.insert(pos, name);

                    // Open the auto-completion popup.
                    this.editor.execCommand("startAutocomplete");

                    // Set the selected column to empty, in preparation of a column selection.
                    //this.selectedColumnName = "";
                }
            },

            insertColumn: function(name) {

                // Set focus to the Editor.
                this.editor.focus();

                if(name !== "")
                {
                    // ...THEN, start by getting the Editor's cursor position, line and previous character.
                    let pos     = this.editor.getCursorPosition();
                    let line    = this.editor.session.getLine(pos.row);
                    let prev    = line.charAt(pos.column - 1);

                    // Get the previous word and determine if it is a valid "table" name.
                    let word    = /(\w*)$/.exec(line)[1];
                    let isTable   = (word && this.tableNames.includes(word));

                    // IF the cursor is NOT at the beginning of the line AND the previous character is NOT a "space"...
                    if(pos.column !== 0 && prev !== " ")
                    {
                        // ...THEN prepend a "."" or " ", depending on if the previous word is a valid "table" name.
                        name = (isTable ? "." : " ") + name;
                    }

                    // Insert the "column" name into the Editor at the current position.
                    this.editor.session.insert(pos, name);

                    // Set the selected column to empty, in preparation of another column selection.
                    //this.selectedColumnName = "";
                }



            },


            /**
             *
             */
            resetCompleters: function()
            {
                // NOTE: here we re-initialize the auto-completion handlers to the defaults, as it is the only way to
                // remove our custom handlers added since the last initialization.

                if(!this.langTools)
                    this.langTools = ace.require("ace/ext/language_tools");

                // Simply empty the Editor's auto-completion handlers.
                this.langTools.setCompleters([
                    this.langTools.snippetCompleter,
                    this.langTools.textCompleter,
                    this.langTools.keyWordCompleter
                ]);

            },

            showCompleter: function()
            {
                if (!this.editor.completer)
                {

                    //this.editor.focus();
                }

                this.editor.execCommand("startAutocomplete");
            },

            hideCompleter: function()
            {
                if(this.editor.completer)
                {
                    this.editor.completer.detach();
                    //this.editor.focus();
                }
            },



            resetPgsqlData: function()
            {
                this.tableNames = [];
                this.selectedTableName = "";
                this.lastTableName = "";
                this.columnNamesByTable = [];
                this.selectedColumnName = "";
                this.lastColumnName = "";
            },


            /**
             *
             */
            mapPgsqlSchema: function()
            {
                // Set a function-wide instance of "this" to avoid collisions.
                let self = this;

                this.tablesLoading = true;

                // Initiate an AJAX request to GET the database schema from the back-end API...
                self.request = $.ajax({
                    url: "public.php?/api/psql/schemas",
                    method: "GET",

                    // Handle successful data acquisition...
                    success: function(data)
                    {
                        // Clear the current "pgsql" mode lookups, as this function re-populates them!
                        self.tableNames = [];
                        self.selectedTableName = "";
                        self.lastTableName = "";
                        self.columnNamesByTable = [];
                        self.column = "";
                        self.lastColumnName = "";

                        // Initialize a local copy of the completion map.
                        let map = [];

                        // Loop through each row in the resulting dataset...
                        data.forEach(
                            function(column)
                            {
                                // NOTE: Here we build the "tables" lookup at the same time to improve performance!

                                // IF the "tables" lookup does NOT already contain this table...
                                if(!self.tableNames.includes(column["table_name"]))
                                {
                                    // ...THEN add this to the "tables" list...
                                    self.tableNames.push(column["table_name"]);

                                    // ...AND also add this table to the completion map.
                                    map.push({
                                        caption: "",
                                        value: column["table_name"],
                                        meta: "Table",
                                        score: 1200
                                    });
                                }

                                // NOTE: Here we build the "columns" lookup at the same time to improve performance!

                                // IF the "columns" lookup does NOT already contain this table index, THEN add it!
                                if(!self.columnNamesByTable.hasOwnProperty(column["table_name"]))
                                    self.columnNamesByTable[column["table_name"]] = [];

                                // Add this column's information to the "columns" lookup!
                                self.columnNamesByTable[column["table_name"]].push({
                                    name: column["column_name"],
                                    type: column["udt_name"],
                                    nullable: column["is_nullable"]
                                });

                                // Add this column to the completion map.
                                map.push({
                                    caption: "",
                                    value: column["column_name"],
                                    meta: "(" + column["udt_name"] + (column["is_nullable"] ? "" : "*") + ")" +
                                        " Column",
                                    score: 1100
                                });

                                // We might as well also add the combination of "table.column" to the completion map.
                                map.push({
                                    caption: "",
                                    value: column["table_name"] + "." + column["column_name"],
                                    meta: "(" + column["udt_name"] + (column["is_nullable"] ? "" : "*") + ")" +
                                        " Table.Column",
                                    score: 1000
                                });
                            }
                        );

                        // NOTE: We leave the mappings in this lookup, even though the individual modes are re-mapped
                        // when the mode is changed, on the off chance we need to do a cross-mode lookup.

                        // Add the completion map to the "map" lookup.
                        self.map["pgsql"] = map;

                        //self.resetCompleters();

                        // Create a completer to handle AutoCompletion for the "pgsql" mode...
                        self.langTools.addCompleter({

                            // Handles the completion lookup performed by the Editor.
                            getCompletions: function(editor, session, pos, prefix, callback)
                            {
                                // IF no characters have been passed, THEN there is no need to perform a lookup!
                                if (prefix.length === 0) {
                                    callback(null, []);
                                    return;
                                }

                                // OTHERWISE, perform a lookup against the completion map!
                                callback(null, self.map["pgsql"]);
                            }
                        });

                        // Trigger a refresh of the auto completion system.
                        self.editor.session.setMode("ace/mode/pgsql");

                        //self.editor.focus();

                        // Indicate loading and the request are complete.
                        self.tablesLoading = false;
                        self.request = null;
                    },

                    // Handle unsuccessful data acquisition...
                    error: function(error)
                    {
                        // IF the operation was aborted, THEN ignore, OTHERWISE log the error to the browser console!
                        if(error.statusText !== "abort")
                            console.log(error);
                    }
                });
            },







            /*
            scanColumns: function(table) {

                console.log(table);

                let self = this;

                self.request = $.ajax({
                    url: "public.php?/api/psql/tables/" + table,
                    method: "GET",
                    success(data) {

                        //console.log(data);

                        // Assign the resulting dataset to the current data.
                        self.columnNamesByTable = data;
                        self.selectedColumnName = data[0].name;

                        // Indicate loading and request are complete.
                        self.loading = false;
                        self.request = null;
                    },
                    error(error) {

                        // IF the operation was aborted, THEN ignore, OTHERWISE log the error to the browser console!
                        if(error.statusText !== "abort")
                            console.log(error);
                    }
                });

            },
            */

            getLineHeight: function()
            {
                let $editor = $("#" + this.id);

                let $div = $("<div>")
                    .css("font-family", "monospace")
                    .css("font-size", "12px")
                    .text("A")
                    .css("padding", 0)
                    .hide();

                $editor.append($div);
                let lineHeight = $div.outerHeight();
                $div.remove();

                return lineHeight;
            },



            setHeightByLines: function(lines)
            {
                let lineHeight = this.getLineHeight();

                let $editor = $("#" + this.id);
                $editor.css("height", (lineHeight * lines) + "px !important");

                let $container = $("#" + this.id + "-container");
                let padding =
                    parseInt($container.css("padding-top").replace("px", "")) +
                    parseInt($container.css("padding-bottom").replace("px", ""));

                let desiredHeight = (lineHeight * lines + padding) + "px";

                $container.css("height", desiredHeight);

                this.editor.setOption("minLines", lines);
                this.editor.setOption("maxLines", lines);
            },

            setBackgroundThemeContrast: function(current = null)
            {

                if (!this.useBootstrap)
                    return;

                if (current === null)
                    current = this.simpleTheme;

                let isDark = this.themes[this.getSimpleTheme(current)].isDark;

                let $container = $("#" + this.id + "-container");
                $container.removeClass(isDark ? "bg-dark" : "bg-light");
                $container.addClass(isDark ? "bg-light" : "bg-dark");
            }





        },

        // =============================================================================================================
        // VUE LIFE-CYCLE HOOKS
        // =============================================================================================================

        /**
         *
         */
        created: function()
        {
            // Using Parent's created() function!
        },

        /**
         *
         */
        mounted: function()
        {
            //console.log(this.mode);
           // console.log(this.theme);


            // Using Parent's mounted() function!

            // Create an instance of the Ace Editor.
            this.editor = ace.edit(this.id);

            //console.log(this.mode);

            // Default Editor options...
            this.editor.setOptions({
                printMargin: false,
                tabSize: 4,
                useSoftTabs: true
            });

            // Default Session options...
            this.editor.session.setOptions({
                newLineMode: "unix",
                tabSize: 4,
                useSoftTabs: true
            });

            // Set the Editor's content from the parent's "v-model" directive and initialize the oldContent value.
            this.editor.setValue(this.oldContent = this.value, 1);

            // Proxy the Editor's "change" event to the parent component, to handle the "v-model" binding...
            this.editor.on("change", $.proxy(function() {

                // Get the Editor's current content and save the oldContent value.
                let content = this.oldContent = this.editor.getValue();

                // Emit the "input" event upstream to the parent!
                this.$emit("input", content);

            }, this));

            // Do this after all of the options have been set, in case any of them cause issues with the sizing!
            //this.editor.resize();

            this.editor.focus();

            // NOTE: Here we enable the Editor's auto-completion system...

            // Import the language tools.
            this.langTools = ace.require("ace/ext/language_tools");

            //noinspection SpellCheckingInspection
            this.editor.setOption("enableBasicAutocompletion", true);
            this.editor.setOption("enableSnippets", true);
            // noinspection SpellCheckingInspection
            this.editor.setOption("enableLiveAutocompletion", true);

            this.modes = this.sortObjectByKeys(ace.require("ace/ext/modelist").modesByName);
            this.themes = this.sortObjectByKeys(ace.require("ace/ext/themelist").themesByName);

            // Set the Editor's "mode", as optionally provided by the ":mode" prop...
            this.localMode = this.fullMode;

            // Set the Editor's "theme", as optionally provided by the ":theme" prop...
            this.localTheme = this.fullTheme;

            //console.log(this.fullMode);
            //console.log(this.fullTheme);


            //this.editor.setOption("maxLines", 10);
            this.editor.setOption("fontSize", 12);
            this.editor.setOption("fontSize", 12);






            //let lines = this.editor.session.getLength() + 2; // Always an extra 2 ???






            // Emit an "initialized" event upstream to the parent!
            this.$emit("initialized", this);



            // Initialize BootStrap's Tooltips
            $(function() {
                $("[data-toggle='tooltip']").tooltip();
            });



        },

        updated: function()
        {
            this.setHeightByLines(10);
            //console.log("update");


        }



    }

</script>


<style>



    .editor {
        width: 100%;
        height: 100px;

    }




    .ace_editor * {
        font-family: monospace;
        font-size: 12px;
        color: inherit;
    }



</style>
