<template>
    <div>

        <Editor
            v-model="content1"
            :id="'editor1'"
            :mode="mode1"
            @modeChanged="refresh"
            :theme="theme1"
            @themeChanged="theme1 = arguments[0]"
            :useBootstrap="true"
        ></Editor>

        <div class="mb-3"></div>

        <Editor
            v-model="content2"
            :id="'editor2'"
            :mode="mode2"
            @modeChanged="refresh"
            :theme="theme2"
            @themeChanged="theme2 = arguments[0]"
        ></Editor>

        <div class="mb-3"></div>

        <button class="btn btn-primary" @click="theme2 = 'ace/theme/dawn'">Dawn</button>
        <button class="btn btn-primary" @click="mode2 = 'ace/mode/text'">Text</button>


    </div>
</template>

<script>




    import Editor from "../components/AceEditor/Editor";

    export default {

        name: "EditorDemo",

        components: {
            Editor
        },

        data () {
            return {

                mode1: "ace/mode/pgsql",
                theme1: "ace/theme/chrome",

                mode2: "ace/mode/php",
                theme2: "ace/theme/cobalt",

                content1: "SELECT * FROM test;",
                content2: "<?php echo $test; ?>",


            }
        },

        methods: {

            refresh: function(mode)
            {
                this.mode = mode;
                //console.log(mode);

            },

        },

        created() {
            console.log("EditorDemo");
        },

        mounted() {
        }

    }

</script>

<!-- Add "scoped" attribute to limit CSS to this component only -->
<style>



</style>
