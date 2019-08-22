<template>
    <div class="page-container">
        <div class="page-content container-fluid h-100 p-2 p-sm-3">
            <slot></slot>
        </div>
    </div>
</template>

<script>

    export default {
        name: "Page",
        data() {
            return {
                pluginName: "Plugin Template (VueJS)",
                githubRepo: "https://github.com/ucrm-plugins/plugin-template-vue"
            }
        }
    }

</script>

<!-- Add "scoped" attribute to limit CSS to this component only -->
<style scoped>

    .page-container {
        flex: auto;
        overflow-y: hidden;
        background-color: #edf0f3;
    }

    .page-content {
        overflow-y: auto;
    }

</style>
