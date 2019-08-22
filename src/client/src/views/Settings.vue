<template>
    <div class="hello">
        <h3>Settings</h3>

        <PermissionGroups
            :start-expanded="true"
        ></PermissionGroups>

        <!--
        <button class="btn btn-primary" @click="items = items2">Change</button>
        -->



        <div class="md:flex bg-blue-500">
            <div class="md:flex-shrink-0">
                <img class="rounded-lg md:w-56" src="https://images.unsplash.com/photo-1556740738-b6a63e27c4df?ixlib=rb-1.2.1&ixid=eyJhcHBfaWQiOjEyMDd9&auto=format&fit=crop&w=448&q=80" alt="Woman paying for a purchase">
            </div>
            <div class="mt-4 md:mt-0 md:ml-6">
                <div class="uppercase tracking-wide text-sm text-indigo-600 font-bold">Marketing</div>
                <a href="#" class="block mt-1 text-lg leading-tight font-semibold text-gray-900 hover:underline">Finding customers for your new business</a>
                <p class="mt-2 text-gray-600">Getting a new business off the ground is a lot of hard work. Here are five ideas you can use to find your first customers.</p>
            </div>
        </div>


    </div>
</template>

<script>

    import axios from "axios";

    import PermissionGroups from "../components/PermissionBuilder/PermissionGroups";

    export default {

        name: "Settings",
        components: {
            PermissionGroups
        },

        watch: {


        },


        data () {
            return {
                selected: [ "Admin Group", "Technicians", "Operators", "Assistants", "Secretaries", "Admin Group" ],
                tags: [ "Admin Group", "Operators", "Technicians" ],


                selectedIndex: -1,

                allowed: [],

                items: [
                    "Admin Group",
                    "Managers",
                    "Technicians",
                    "Operators",
                    "Installers",
                    "Assistants",
                    "Secretaries",
                    "Enterprise Clients",
                    "Interns",
                ],

                items2: [
                    "Admin Group",
                    "Managers",
                    "Technicians",
                    "Enterprise Clients",
                    "Interns",
                ],

            }
        },


        created: function()
        {
            console.log("Settings");




        },


    }

</script>



<!-- Add "scoped" attribute to limit CSS to this component only -->
<style lang="stylus" scoped>

    @tailwind base;
    @tailwind components;
    @tailwind utilities;


    .list-group-item
        padding 0.5rem

        & i
            width 24px
            text-align center
            margin-right 0.5rem




</style>
