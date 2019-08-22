<template>
    <div id="ptv-header-container" class="px-3 px-sm-4"  :style="[ hasToolbar() ? { paddingBottom: '0' } : {} ]">
        <div id="ptv-header" class="text-left d-flex flex-column flex-sm-row justify-content-between p-0 mb-3 mb-sm-0">
            <!-- Plugin Name -->
            <h1 class="mb-2 mb-sm-0">{{ pluginName }}</h1>
            <div class="d-flex flex-row justify-content-between">
                <div class="">
                    <!-- Github Link (disabled if none provided) -->
                    <a
                        class="btn btn-sm btn-outline-secondary"
                        :class="{ disabled: !githubRepo }"
                        :href="githubRepo"
                        target="_blank">
                        <!--<img class="button-icon" src="../assets/images/github/logo-32px.png" alt="GitHub Logo">-->
                        <i class="fab fa-github"></i>
                    </a>
                    <!-- Donate Link (disabled if none provided) -->
                    <a
                        class="btn btn-sm btn-outline-success ml-2"
                        :class="{ disabled: !donateLink }"
                        :href="donateLink"
                        target="_blank">
                        Donate
                    </a>
                </div>

                <div class="d-flex d-sm-none">
                    <!-- Menu Toggle -->
                    <a
                        class="btn btn-sm btn-outline-secondary"
                        data-toggle="collapse"
                        data-target="#ptv-header-menu"
                        href="#ptv-header-menu"
                        role="button">

                        <i class="fas fa-bars"></i>
                    </a>

                </div>

            </div>
        </div>

        <div id="ptv-header-menu" class="collapse dont-collapse-sm in">

            <div id="ptv-toolbar" v-if="hasToolbar()" class="d-flex flex-column flex-sm-row justify-content-between">

                <ul class="nav nav-tabs d-flex flex-column flex-sm-row text-center text-sm-left mt-0 mt-sm-2">
                    <li class="nav-item mr-0 mr-sm-5" v-for="item in nav.left" @click="menuCollapse">
                        <router-link class="nav-link d-flex flex-row justify-content-between" :to="item.link">
                            {{ item.name }}
                            <div v-if="item.badge">
                                <span class="nav-badge rounded"><small>{{ item.badge }}</small></span>
                            </div>

                        </router-link>
                        <div class="nav-underline"></div>
                    </li>
                </ul>

                <ul class="nav nav-tabs d-flex flex-column flex-sm-row text-center text-sm-left mt-0 mt-sm-2">
                    <li class="nav-item mr-0 mr-sm-5" v-for="item in nav.right" @click="menuCollapse">
                        <router-link class="nav-link d-flex flex-row justify-content-between " :to="item.link">
                            <span v-if="item.name">{{ item.name }}</span>

                            <span v-if="item.icon">
                            <i :class=
                                   "[
                                    { 'fas': item.icon }, item.icon,
                                    { 'ml-2': item.name },
                                    {'mx-sm-3': !item.name}, { 'mx-0': !item.name }
                                ]">
                            </i>
                        </span>

                            <div v-if="item.badge">
                                <span class="nav-badge rounded"><small>3</small></span>
                            </div>

                        </router-link>
                        <div class="nav-underline"></div>
                    </li>
                </ul>
            </div>



        </div>

    </div>
</template>

<script>


export default {
    name: "Header",
    data() {
        return {
            pluginName: "Plugin Template (VueJS)",
            githubRepo: "https://github.com/ucrm-plugins/plugin-template-vue",
            donateLink: "https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=YGDTYH2P6WJNN&source=url",
            nav: {
                left: [
                    {
                        name: "AceEditor",
                        link: "/editor",
                        badge: ""
                    },
                    {
                        name: "QueryBuilder",
                        link: "/query",
                        badge: ""
                    },
                    {
                        name: "HtmlDesigner",
                        link: "/designer",
                        badge: "2"
                    }
                ],
                right: [
                    {
                        name: "Logs",
                        //icon: "fa-cog",
                        link: "/logs",
                        badge: ""
                    },
                    {
                        name: "Settings",
                        //icon: "fa-cog",
                        link: "/settings",
                        badge: ""
                    }
                ]
            }
        }
    },
    computed: {

    },
    methods: {
        hasToolbar() {
            return (
                this.nav &&
                (this.nav.left && this.nav.left.length > 0) ||
                (this.nav.right && this.nav.right.length > 0)
            );
        },

        menuCollapse() {
            $("#ptv-header-menu").collapse("hide");
        }

    }
}
</script>

<!-- Add "scoped" attribute to limit CSS to this component only -->
<style>

    #ptv-header-container {
        flex: none;
        display: block;
        margin: 0;
        background: #fff;
        /* box-shadow: 0 0 1px 0 rgba(0,0,0,.1); */
        padding: 15px 32px;
    }

    #ptv-header {

    }

    #ptv-header > h1 {
        margin: 0;
        color: #4c4c4c;
        font-size: 22px;
        line-height: 1.2;
        font-weight: 300;
    }




    .button-icon {
        height:16px;
    }

    #ptv-toolbar {
        //margin-top: 2px;
    }

    #ptv-toolbar > ul.nav-tabs {
        border: none;
        margin-top: 16px;
        margin-bottom: 2px;
    }

    #ptv-toolbar .nav-item:last-child {
        margin-right: 0 !important;
    }

    #ptv-toolbar .nav-link {
        color: #424242;
        margin-bottom: 2px;
        padding-left: 0;
        padding-right: 0;
        font-size: 1rem;
        font-weight: 300;
        color: black;
    }

    .nav-underline {
        height: 3px;
    }

    .router-link-exact-active + .nav-underline {
        background-color: #00a0df;
        height: 3px;
    }

    .nav-badge {
        color: #fff;
        background-color: #f29400;
        margin-left: 6px;
        padding: 1px 6px;
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
    }


</style>
