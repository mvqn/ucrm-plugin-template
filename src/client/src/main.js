import Vue from 'vue';
import App from './App.vue';
import Router from "./router";


Vue.config.productionTip = false;

new Vue({
    router: Router,
    render: h => h(App),
    mounted: function()
    {
        //let q = this.$route;
        //console.log(this.$route);
    },
}).$mount('#app');


/* eslint-disable no-new */
/*
new Vue({
    el: '#app',
    router,
    components: { App },
    template: '<App/>'
});
*/
