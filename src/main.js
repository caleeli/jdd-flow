import Vue from 'vue'
import App from './App.vue'
import VueRouter from 'vue-router';
import axios from './mocks';
import HelloWorld from './components/HelloWorld.vue';
import HelloTask from './components/HelloTask.vue';

Vue.config.productionTip = false
Vue.use(VueRouter);
window.axios = axios;

const router = new VueRouter({
  routes: [
    {path: '/', component: HelloWorld},
    {path: '/task', component: HelloTask},
  ]
});
window.router = router;

new Vue({
  router,
  render: h => h(App),
}).$mount('#app')
