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
			uni.getSystemInfo({
				success: function(e) {
					// #ifndef MP
					Vue.prototype.StatusBar = e.statusBarHeight;
					if (e.platform == 'android') {
						Vue.prototype.CustomBar = e.statusBarHeight + 50;
					} else {
						Vue.prototype.CustomBar = e.statusBarHeight + 45;
					};
					// #endif
		
					// #ifdef MP-WEIXIN
					Vue.prototype.StatusBar = e.statusBarHeight;
					let custom = wx.getMenuButtonBoundingClientRect();
					Vue.prototype.Custom = custom;
					Vue.prototype.CustomBar = custom.bottom + custom.top - e.statusBarHeight;
					// #endif		
		
					// #ifdef MP-ALIPAY
					Vue.prototype.StatusBar = e.statusBarHeight;
					Vue.prototype.CustomBar = e.statusBarHeight + e.titleBarHeight;
					// #endif
				}
			})
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

