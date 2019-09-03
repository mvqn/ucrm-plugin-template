<template>
    <div id="app" class="d-flex flex-column">
        <Header />
        <Page>
            <router-view/>
        </Page>
    </div>
</template>

<script>

    //import api from "./services/api";

    import Header from "./components/common/Header";
    import Page from "./components/common/Page";

    export default {
        name: "App",
        components: {
            Header,
            Page
        },
        created() {
            //console.log(this.$route);

            this.$api.getMode()
                .then($.proxy(function(mode)
                {
                    if(mode === "development")
                    {
                        console.warn("Development Mode is active!");
                    }

                }, this));
        }
    }

</script>

<style lang="stylus">

    /*noinspection CssUnknownTarget*/
    @import url("https://fonts.googleapis.com/css?family=Lato:300,400,700&display=swap");


    * {
        //font-family: Lato, "Helvetica Neue", Helvetica, Arial, sans-serif;
    }

    html, body
        height: 100vh;
        min-width: 320px;
        margin: 0;
        padding: 0;
        //position: relative;
        //background-color: #edf0f3;
        overflow-x: hidden;
        -webkit-font-smoothing: antialiased;
        -moz-osx-font-smoothing: grayscale;


    #app {
        height: 100%;
        overflow-y: hidden;
    }

</style>
