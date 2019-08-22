<template>
    <div id="app" class="d-flex flex-column">
        <Header />
        <Page>
            <router-view/>
        </Page>
    </div>
</template>

<script>

    import Header from "./components/Header";
    import Page from "./components/Page";

    export default {
        name: "App",
        components: {
            Header,
            Page
        },
        created() {
            //console.log(this.$route);
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
