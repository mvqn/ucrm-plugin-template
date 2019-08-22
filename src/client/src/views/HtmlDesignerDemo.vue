<!--suppress JSUnusedLocalSymbols, JSUnresolvedVariable -->
<template>
    <div class="h-100">

        <HtmlDesigner v-model="html" :dynamics="dynamics"></HtmlDesigner>


    </div>
</template>

<script>

    import HtmlDesigner from "../components/HtmlDesigner/HtmlDesigner";


    export default {

        name: "HtmlDesignerDemo",

        components: {
            HtmlDesigner


        },

        data () {
            return {

                editor: null,

                html: "",

                dynamics: {},

            }
        },

        watch: {




        },

        computed: {


        },


        methods: {




        },

        created() {
            console.log("HtmlDesignerDemo");

            this.dynamics =
            {
                admin:
                {
                    id: 1,
                    firstName: "Ryan",
                    lastName: "Spaeth",
                },
                user:
                {
                    id: 2,
                    firstName: "Michelle",
                    lastName: "Spaeth",
                },
                ticket:
                {
                    id: 1234,
                    adminId: 1,
                    userId: 2,
                    subject: "This is a test"
                }

            }



        },

        mounted: function()
        {


        }

    }

</script>

<!-- Add "scoped" attribute to limit CSS to this component only -->
<style>



</style>
