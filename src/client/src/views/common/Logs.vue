<template>
    <!------------------------------------------------------------------------------------------------------------------
    Logs (VueJS Component)

    A component for interacting with and displaying the Plugin's log files and tables.

    @author Ryan Spaeth <rspaeth@mvqn.net>
    ------------------------------------------------------------------------------------------------------------------->
    <div
        class="d-flex flex-column h-100">

        <!--------------------------------------------------------------------------------------------------------------
        LOGS HEADER
        --------------------------------------------------------------------------------------------------------------->
        <div
            id="logsHeader"
            class="d-flex flex-column mb-3 mb-sm-0">

            <!----------------------------------------------------------------------------------------------------------
            LOGS NAVIGATION (MOBILE ONLY)
            ----------------------------------------------------------------------------------------------------------->
            <div
                class="d-flex d-sm-none flex-row justify-content-between">

                <!-- Log Selector -->
                <button
                    class="btn btn-sm btn-outline-info d-sm-none"
                    data-toggle="collapse"
                    data-target="#logsMenu"
                    @click="logsMenuClick">

                    <i :class="[ getIconClasses(current), 'mr-1' ]"></i> {{ name }}
                </button>

                <div
                    class="d-flex flex-row ">

                    <!-- Page Selector -->
                    <button
                        class="btn btn-sm btn-outline-info d-sm-none"
                        data-toggle="collapse"
                        data-target="#logsPagination"
                        @click="logsPaginationClick">

                        # {{ page }}
                    </button>

                    <!-- Log Clear -->
                    <button
                        class="btn btn-sm btn-outline-info d-sm-none ml-2"
                        data-toggle="collapse"
                        data-target="#logsTools"
                        @click="logsToolsClick">

                        <i class="fas fa-tools"></i>
                    </button>
                </div>
            </div>

            <!----------------------------------------------------------------------------------------------------------
            LOGS NAVIGATION
            ----------------------------------------------------------------------------------------------------------->
            <div
                id="logsMenu"
                class="collapse dont-collapse-sm in">

                <ul
                    id="logsNav"
                    class="nav nav-tabs flex-column flex-sm-row mt-1 mt-sm-0 border-primary">

                    <!-- Default tab for use as a placeholder only, while the data is loading! -->
                    <li
                        v-if="meta.length === 0"
                        class="nav-item text-center">

                        <a
                            class="log-tab btn btn-block btn-outline-primary"
                            href="#">

                            <i class="mr-1 fas fa-file"></i> plugin
                        </a>
                    </li>

                    <!-- List of all discovered log files, setting "plugin" active at start. -->
                    <li
                        v-for="(data, name) in meta"
                        class="nav-item text-center mx-0 mt-0 mt-sm-0"
                        :class="{ 'ml-sm-1': name !== 'plugin', 'mt-1': name !== 'plugin' }"
                        :key="name">

                        <a
                            :id="name"
                            class="log-tab btn btn-block btn-outline-primary"
                            :class="{ active: name === 'plugin' }"
                            href="#"
                            @click.prevent="logClick(name)">

                            <i class="mr-1" :class="getIconClasses(data)"></i> {{ name }}
                        </a>
                    </li>
                </ul>
            </div>

            <!----------------------------------------------------------------------------------------------------------
            LOGS PAGINATION
            ----------------------------------------------------------------------------------------------------------->
            <div
                class="d-flex flex-column flex-sm-row justify-content-between">

                <div
                    id="logsPagination"
                    class="d-sm-flex justify-content-between collapse in">

                    <!-- Placeholder content for pagination pane when loading. -->
                    <ul v-if="tablesLoading"
                        class="pagination pagination-sm mt-1 mt-sm-3 justify-content-center d-none d-sm-block">
                        <li class="page-item disabled"><a class="btn btn-sm btn-block btn-outline-info" href="#">Loading...</a></li>
                    </ul>

                    <!-- Pagination Component (https://github.com/lokyoung/vuejs-paginate) -->
                    <Paginate
                        v-if="!tablesLoading"
                        v-model="page"
                        :page-count="tablesLoading ? 0 : (current.totalPages || 0)"
                        :page-range="5"
                        :margin-pages="1"
                        :prev-text="'Prev'"
                        :next-text="'Next'"
                        :break-view-text="'...'"

                        :click-handler="paginateClick"
                        :container-class="'mt-1 mt-sm-3 mb-0 mb-sm-3 d-flex flex-column flex-sm-row'"
                        :page-class="''"
                        :page-link-class="'btn btn-sm btn-outline-info mb-1 mb-sm-0 mr-0 mr-sm-1'"
                        :prev-class="''"
                        :prev-link-class="'btn btn-sm btn-outline-info mb-1 mb-sm-0 mr-0 mr-sm-1'"
                        :next-class="''"
                        :next-link-class="'btn btn-sm btn-outline-info mb-0 mr-0 mr-sm-1'"
                        :break-view-class="''"
                        :break-view-link-class="'btn btn-sm btn-outline-info mb-1 mb-sm-0 mr-0 mr-sm-1'"
                        :active-class="'active'"
                        :no-li-surround="true"
                        >
                    </Paginate>
                </div>

                <div
                    id="logsTools"
                    class="d-sm-flex justify-content-between collapse in">

                    <!-- Tools Section -->
                    <ul
                        v-if="!tablesLoading"
                        class="pagination pagination-sm mt-1 mt-sm-3 mb-0  flex-column flex-sm-row">

                        <!-- Filter / TODO: Complete functionality! -->
                        <li
                            class="mr-sm-1">

                            <a
                                class="btn btn-sm btn-block btn-outline-info mb-1 mb-sm-0 disabled"
                                href="#">

                                <i class="fas fa-filter"></i>
                            </a>
                        </li>

                        <!-- Refresh / TODO: Complete functionality! -->
                        <li
                            class="mr-sm-1">

                            <a
                                class="btn btn-sm btn-block btn-outline-info mb-1 mb-sm-0 disabled"
                                href="#">

                                <i class="fas fa-sync-alt"></i>
                            </a>
                        </li>

                        <!-- Clear log -->
                        <li
                            class="page-item text-center">

                            <a
                                class="btn btn-sm btn-block btn-danger"
                                href="#"
                                data-toggle="modal"
                                data-target="#deleteConfirm">

                                <i class="fas fa-trash-alt"></i>
                            </a>
                        </li>
                    </ul>
                </div>
            </div>

        </div>

        <!--------------------------------------------------------------------------------------------------------------
        LOGS CONTENT
        --------------------------------------------------------------------------------------------------------------->
        <div
            id="logs-content"
            class="d-flex flex-grow-1">

            <!-- Loading Spinner -->
            <div
                v-if="tablesLoading"
                class="h-100 w-100 d-flex justify-content-center">

                <div
                    class="d-flex align-items-center my-auto">

                    <i class="fas fa-spinner fa-spin fa-4x"></i>
                </div>
            </div>

            <!-- Log Entry -->
            <div
                v-if="!tablesLoading" class="logs-entry w-100">

                <div
                    v-for="(entry, index) in current.entries"
                    v-if="!isNullOrEmpty(entry)"
                    :id="'entry-' + index"
                    class="entry"
                    :class="[ getAlertClasses(entry), { 'mb-0': index === current.entries.length -1  } ]">

                    <!--------------------------------------------------------------------------------------------------
                    NON-STANDARD ENTRY
                    --------------------------------------------------------------------------------------------------->
                    <div
                        v-if="current.type === 'raw'"
                        v-html="entry">
                    </div>

                    <!--------------------------------------------------------------------------------------------------
                    STANDARD ENTRY HEADER
                    --------------------------------------------------------------------------------------------------->
                    <div
                        v-if="current.type !== 'raw'"
                        :id="'header-' + index"
                        class="header d-flex flex-column flex-sm-row justify-content-between">

                        <!-- Timestamp -->
                        <div
                            v-if="!isNullOrEmpty(entry.timestamp)"
                            :id="'timestamp-' + index"
                            class="timestamp">
                            {{entry.timestamp}}
                        </div>

                        <div>

                            <!-- Context Collapse Link -->
                            <a
                                v-if="!isNullOrEmpty(entry.context)"
                                :id="'context-link-' + index"
                                class="context-link"
                                data-toggle="collapse"
                                :href="'#context-' + index"
                                role="button"
                                aria-expanded="false"
                                :aria-controls="'context-' + index">

                                Context
                            </a>

                            <!-- Separator -->
                            <span
                                v-if="!isNullOrEmpty(entry.context)"
                                class="header-spacer">

                                |
                            </span>

                            <!-- Extra Collapse Link -->
                            <a
                                v-if="!isNullOrEmpty(entry.extra)"
                                :id="'extra-link-' + index"
                                class="extra-link"
                                data-toggle="collapse"
                                :href="'#extra-' + index"
                                role="button"
                                aria-expanded="false"
                                :aria-controls="'extra-' + index">

                                Extra
                            </a>
                        </div>
                    </div>

                    <!--------------------------------------------------------------------------------------------------
                    STANDARD ENTRY MESSAGE
                    --------------------------------------------------------------------------------------------------->
                    <div
                        v-if="current.type !== 'raw' && !isNullOrEmpty(entry.message)"
                        :id="'message-' + index"
                        class="message"
                        v-html="entry.message">
                    </div>

                    <!--------------------------------------------------------------------------------------------------
                    STANDARD ENTRY CONTEXT
                    --------------------------------------------------------------------------------------------------->
                    <div
                        v-if="current.type !== 'raw' && !isNullOrEmpty(entry.context)"
                        :id="'context-' + index"
                        class="context collapse in">
                        <VueJsonPretty :data="toJson(entry.context)"></VueJsonPretty>
                    </div>

                    <!--------------------------------------------------------------------------------------------------
                    STANDARD ENTRY EXTRA
                    --------------------------------------------------------------------------------------------------->
                    <div
                        v-if="current.type !== 'raw' && !isNullOrEmpty(entry.extra)"
                        :id="'extra-' + index"
                        class="extra collapse in">
                        <VueJsonPretty :data="toJson(entry.extra)"></VueJsonPretty>
                    </div>
                </div>
            </div>
        </div>

        <!--------------------------------------------------------------------------------------------------------------
        DELETE CONFIRM (MODAL)
        --------------------------------------------------------------------------------------------------------------->
        <div
            id="deleteConfirm"
            class="modal"
            tabindex="-1"
            role="dialog"
            data-backdrop="static">

            <div
                class="modal-dialog modal-dialog-centered"
                role="document">

                <div
                    class="modal-content">

                    <div
                        class="modal-header">

                        <h5 class="modal-title">Confirm Delete</h5>
                    </div>

                    <div
                        class="modal-body">

                        <p>Are you sure you want to clear the current log file?</p>
                    </div>

                    <div
                        class="modal-footer">

                        <!-- Delete Button-->
                        <button
                            class="btn btn-primary"
                            type="button"
                            data-dismiss="modal"
                            @click="deleteClick">

                            Delete
                        </button>

                        <!-- Close Button-->
                        <button
                            class="btn btn-secondary"
                            type="button"
                            data-dismiss="modal">

                            Close
                        </button>
                    </div>
                </div>
            </div>
        </div>

    </div>
</template>



<script>
    /**
     *
     *
     */

    import VueJsonPretty from "vue-json-pretty";
    import Paginate from "vuejs-paginate";

    // noinspection JSUnusedLocalSymbols
    export default {

        name: "Logs",

        components: {
            VueJsonPretty,
            Paginate
        },

        data() {
            return {
                // Metadata for ALL logs.
                meta: [],

                // Name of the currently requested log.
                name: "plugin",

                // Page of the currently requested log.
                page: 1,

                // The actual data returned from the currently requested log.
                current: [],

                // Flag to indicate whether the "loading" indicator should be shown.  True, on page load.
                tablesLoading: true,

                // The currently executing AJAX request, null if none are in progress.
                request: null,

                // A flag to denote the first logClick event from subsequent logClick events.
                refresh: false,
            }
        },

        /**
         * Vue Component life-cycle hook triggered upon component creation.
         */
        created() {

            // Do nothing!
            console.log("Logs");

        },

        /**
         * Vue Component life-cycle hook triggered upon component mounting.
         */
        mounted() {

            // Start the page load off by getting the metadata for all of the logs.
            this.getLogs();

        },

        methods: {

            /**
             * Handles the "Delete" button click from the "Confirm Delete" modal dialog.
             */
            deleteClick() {

                let self = this;
                let url = "public.php?/api/logs/";

                // Set the URL based on the log type...
                switch(self.meta[self.name].type) {
                    case "raw":     url += self.current.file;               break;
                    case "txt":     url += self.current.file;               break;
                    case "sql":     url += "db&channel=" + self.name;       break;
                }

                // Append the confirmation, as we have already displayed the modal confirmation dialog.
                url += "&delete=yes";

                // Log the URL to the browser console.
                //console.log(url);

                // Hide the Tools collapse (mobile view only).
                $("#logsTools").collapse("hide");

                // Set the loading flag to display the spinner.
                self.tablesLoading = true;

                // Initiate the AJAX request to the REST endpoint.
                self.request = $.ajax({
                    url: url,
                    method: "DELETE",
                    success(data) {

                        // Assign the resulting dataset to the current data.
                        self.current = data[self.name];

                        // IF the previously selected page is beyond the new total pages, THEN fix it!
                        if(self.page > self.current.totalPages)
                            self.page = self.current.totalPages;

                        // Indicate loading and request are complete.
                        self.tablesLoading = false;
                        self.request = null;
                    },
                    error(error) {

                        // IF the operation was aborted, THEN ignore, OTHERWISE log the error to the browser console!
                        if(error.statusText !== "abort")
                            console.log(error);
                    }
                });
            },

            /**
             * Handles page clicks in the Paginate component.
             */
            paginateClick(page) {

                // Hide the Pagination collapse (mobile view only).
                $("#logsPagination").collapse("hide");

                // Synchronize the log window.
                this.updateLogBox();
            },

            /**
             * Handles clicks in the Menu collapse.
             */
            logsMenuClick() {

                // Hide the Pagination and Tools collapse (mobile view only).
                $("#logsPagination").collapse("hide");
                $("#logsTools").collapse("hide");

                // NOTE: The actual handling is done by Vue using the router-links.
            },

            /**
             * Handles clicks in the Pagination collapse.
             */
            logsPaginationClick() {

                // Hide the Menu and Tools collapse (mobile view only).
                $("#logsMenu").collapse("hide");
                $("#logsTools").collapse("hide");

                // NOTE: The actual handling is done by Paginate component.
            },

            /**
             * Handles clicks in the Tools collapse.
             */
            logsToolsClick() {

                // Hide the Menu and Pagination collapse (mobile view only).
                $("#logsMenu").collapse("hide");
                $("#logsPagination").collapse("hide");

                // NOTE: The actual handling is done in our HTML.
            },

            /**
             * Handles clicks in the Menu.
             */
            logClick: function(name) {

                let self = this;

                // Loop through all links in the Menu...
                $("#logsNav").find("a").each(function() {

                    let $link = $(this);

                    // First, remove the "active" class from any link that has it!
                    if($link.hasClass("active"))
                        $link.removeClass("active");

                    // IF the link is named the same as the current dataset...
                    if($link.attr("id") === name) {

                        // ...THEN add the "active" class to the link.
                        $link.addClass("active");

                        // IF a current AJAX request is executing, THEN abort it!
                        if(self.request !== null)
                            self.request.abort();

                        // Set the currently requested name and page (the last page at start).
                        self.name = name;
                        self.page = self.meta[name].totalPages;

                        // Set the flag indicating the first load of this log.
                        self.refresh = true;

                        // Synchronize the log window.
                        self.updateLogBox();

                        // Hide the Menu collapse (mobile view only).
                        $("#logsMenu").collapse("hide");
                    }
                });
            },



            /**
             * Converts the data to a JSON object to be consumed by the VueJsonPretty component.
             */
            toJson(data) {

                // First, convert the data to a JSON string.
                let str = JSON.stringify(data);

                // And then back to a JSON object and return!
                return JSON.parse(str);
            },

            /**
             * Returns true if the provided content is invalid or empty, otherwise false.
             */
            isNullOrEmpty(content) {
                return content === undefined || content === null || content.length === 0;
            },



            /**
             * Gets the metadata for all of the logs.
             */
            getLogs() {

                let self = this;
                let url = "public.php?/api/logs";

                // Set the loading flag to display the spinner.
                self.tablesLoading = true;

                // Initiate the AJAX request to the REST endpoint.
                self.request = $.ajax({
                    url: url,
                    method: "GET",
                    success(logs) {

                        //console.log("getLogs()", url, logs);

                        // Update the metadata for all logs.
                        self.meta = logs;

                        // Update the currently requested page to that of the last page of the currently requested log.
                        self.page = logs[self.name].totalPages;

                        // Synchronize the log window.
                        self.updateLogBox();

                        // Indicate loading and request are complete.
                        self.tablesLoading = false;
                        self.request = null;
                    },
                    error(error) {

                        // IF the operation was aborted, THEN ignore, OTHERWISE log the error to the browser console!
                        if(error.statusText !== "abort")
                            console.log(error);
                    }
                });

            },

            /**
             * Gets the actual data for the specified log, by URL.
             */
            getLog(link) {

                let self = this;
                //let url = "";

                //console.log(link);

                //if(link === "../plugin.log")
                //    url = "public.php?/api/logs/plugin.log";
                //else
                let url = "public.php?/api/logs/" + link;


                // Set the loading flag to display the spinner.
                self.tablesLoading = true;

                // Initiate the AJAX request to the REST endpoint.
                self.request = $.ajax({
                    url: url,
                    method: "GET",
                    success(data) {

                        //console.log("getLog(" + link + ")", url, data);

                        // IF the resulting dataset contains information for the currently requested log...
                        if(data.hasOwnProperty(self.name)) {

                            // ...THEN update the dynamic metadata for this log only!
                            self.meta[self.name].rows = data[self.name].rows;
                            self.meta[self.name].totalPages = data[self.name].totalPages;

                            // And set the current dataset to these results.
                            self.current = data[self.name];

                            // IF this is the first load of the log...
                            if(self.refresh) {

                                // ...THEN set the page to the last page and set the first load as handled!
                                self.page = data[self.name].totalPages;
                                self.refresh = false;
                            }
                        }

                        // Indicate loading and request are complete.
                        self.tablesLoading = false;
                        self.request = null;
                    },
                    error(error) {

                        // IF the operation was aborted, THEN ignore, OTHERWISE log the error to the browser console!
                        if(error.statusText !== "abort")
                            console.log(error);
                    }
                });
            },

            /**
             * Handles synchronization of the current log page with the currently requested log.
             */
            updateLogBox() {

                let requested = this.meta[this.name];
                let url = "";

                // Build the URL based on the log type...
                switch(requested.type) {

                    case "raw":
                        url = requested.file + "&page=" + this.page;
                        break;

                    case "txt":
                        url = requested.file + "&page=" + this.page;
                        break;

                    case "sql":
                        url = "db&channel=" + (this.name === "plugin" ? "ucrm" : this.name) + "&page=" + this.page;
                        break;

                    default:
                        return;
                }

                // IF the URL is valid, then initiate a request to the REST endpoint...
                if(url !== "")
                    this.getLog(url);
            },



            /**
             * Gets the alert classes for the specified entry.
             */
            getAlertClasses(entry) {

                // IF the entry does not contain the expected data, THEN return a default set of classes.
                if(!entry || !entry.hasOwnProperty("levelName"))
                    return "alert alert-secondary";

                // NOTE: Set the classes to your liking below...
                switch(entry["levelName"].toUpperCase()) {

                    case "DEBUG"    : return "alert alert-success";
                    case "INFO"     : return "alert alert-info";
                    case "NOTICE"   : return "alert alert-primary";
                    case "WARNING"  : return "alert alert-warning";
                    case "ERROR"    : return "alert alert-danger";
                    case "CRITICAL" : return "alert alert-danger";
                    case "ALERT"    : return "alert alert-danger";
                    case "EMERGENCY": return "alert alert-danger";

                    default         : return "alert alert-secondary";
                }
            },

            /**
             * Gets the icon classes for the specified entry.
             */
            getIconClasses(entry) {

                // IF the entry does not contain the expected data, THEN return a default set of classes.
                if(!entry || !entry.hasOwnProperty("type"))
                    return (this.name === "plugin" ? "fas fa-file" : ""); // For first page load!

                // NOTE: Set the icons to your liking below...
                switch(entry["type"].toLowerCase()) {

                    case "raw"      : return "far fa-file";
                    case "txt"      : return "fas fa-file";
                    case "sql"      : return "fas fa-database";

                    default         : return "";
                }
            },

        }
    }

</script>



<!-- Add "scoped" attribute to limit CSS to this component only -->
<style scoped>

    #logs-content {
        overflow-y: auto;
    }

    .timestamp {
        font-style: italic;
    }

    .nav-tabs {
        border-bottom: none;
    }

    @media (min-width: 576px) {
        .collapse.dont-collapse-sm {
            display: block;
            height: auto !important;
            visibility: visible;
        }

        .nav-tabs {
            border-bottom: 1px solid #dee2e6;
        }
    }

    .log-tab {
        -webkit-border-radius: 0.25rem 0.25rem 0 0 !important;
        -moz-border-radius: 0.25rem 0.25rem 0 0 !important;
        border-radius: 0.25rem 0.25rem 0 0 !important;
    }

    @media (max-width: 575px) {
        .log-tab {
            -webkit-border-radius: 0 !important;
            -moz-border-radius: 0 !important;
            border-radius: 0 !important;
        }
    }

</style>
