<script>
	import Vue from 'vue'
	export default {
		created() {
			// #ifdef APP-PLUS
			plus.navigator.closeSplashscreen();
			// #endif 
		},
		onLaunch: function() {
			console.log('App Launch')
			// #ifndef MP
			let systemInfo = {}
			try {
				systemInfo = uni.getSystemInfoSync ? (uni.getSystemInfoSync() || {}) : {}
			} catch (error) {
				systemInfo = {}
			}
			Vue.prototype.StatusBar = systemInfo.statusBarHeight || 0;
			if (systemInfo.platform == 'android') {
				Vue.prototype.CustomBar = (systemInfo.statusBarHeight || 0) + 50;
			} else {
				Vue.prototype.CustomBar = (systemInfo.statusBarHeight || 0) + 45;
			}
			// #endif

			// #ifdef MP-WEIXIN
			let windowInfo = {};
			try {
				windowInfo = typeof wx.getWindowInfo === 'function' ? (wx.getWindowInfo() || {}) : {};
			} catch (error) {
				windowInfo = {};
			}
			const statusBarHeight = windowInfo.statusBarHeight || 0;
			Vue.prototype.StatusBar = statusBarHeight;
			let custom = null;
			try {
				custom = typeof wx.getMenuButtonBoundingClientRect === 'function' ? wx.getMenuButtonBoundingClientRect() : null;
			} catch (error) {
				custom = null;
			}
			Vue.prototype.Custom = custom;
			Vue.prototype.CustomBar = custom && custom.bottom ? (custom.bottom + custom.top - statusBarHeight) : (statusBarHeight + 45);
			// #endif

			// #ifdef MP-ALIPAY
			Vue.prototype.StatusBar = systemInfo.statusBarHeight || 0;
			Vue.prototype.CustomBar = (systemInfo.statusBarHeight || 0) + (systemInfo.titleBarHeight || 0);
			// #endif
		},
		onShow: function() {
			console.log('App 开启')
		},
		onHide: function() {
			console.log('App 关闭')
		}
	}
</script>

<style >
	@import "colorui/main.css";
	@import "colorui/icon.css";	
	page {
		--school-theme-primary: #8FBFF6;
		--school-theme-secondary: #C9E0FF;
		--school-theme-text: #111827;
	}
	.text-purple {
		color: var(--school-theme-primary) !important;
	}
	.bg-purple {
		background-color: var(--school-theme-primary) !important;
		color: #fff !important;
	}
	.bg-gradual-purple {
		background-color: var(--school-theme-primary) !important;
		background-image: linear-gradient(135deg, var(--school-theme-primary) 0%, var(--school-theme-secondary) 100%) !important;
		color: #fff !important;
	}
	.line-purple {
		color: var(--school-theme-primary) !important;
		border-color: var(--school-theme-primary) !important;
	}
	.flex_layout{display: flex;align-items: center;flex-wrap: wrap;}
</style>

