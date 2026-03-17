import Vue from 'vue'
import App from './App'
import * as Api from './config/api.js' 
import * as Common from './config/common.js' 
import * as Db from './config/db.js'
import * as Config from './config/config.js'
import cuCustom from './colorui/components/cu-custom.vue'
import themeMixin from './common/theme-mixin.js'
Vue.component('cu-custom',cuCustom)
Vue.mixin(themeMixin)

Vue.prototype.$api = Api;
Vue.prototype.$common = Common;
Vue.prototype.$db = Db;
Vue.prototype.$config = Config;

Vue.config.productionTip = false

App.mpType = 'app'

const app = new Vue({
    ...App
})
app.$mount() 

