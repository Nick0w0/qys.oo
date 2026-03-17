<template>
	<view :style="themeVarsStyle">  
			<view style="height:75px"></view>
			<view class="cu-bar tabbar bg-white shadow foot">
				
				<view class="action" :class="footerTab=='0'?'text-purple':'text-gray'" @tap="clickTabPath('/pages/index/index')"> 
					<view class="cuIcon-homefill"></view> 首页
				</view>
				
				<view class="action" :class="footerTab=='1'?'text-purple':'text-gray'" @tap="clickTabPath('/pages/index/hot')">
					<view class="cuIcon-hotfill"></view> 动态
				</view>
				
				<view class="action add-action" :class="footerTab=='2'?'text-purple':'text-gray'" @tap="clickTabPath('/pages/index/publish')">
					<button class="cu-btn cuIcon-add bg-gradual-purple shadow"></button>
					发布
				</view>
				
				<view class="action" :class="footerTab=='3'?'text-purple':'text-gray'" @tap="clickTabPath('/pages/user/message')">
					<view class="cuIcon-notice">
						 <view class="cu-tag badge"  v-if="messageCount>99">99+</view>
						<view class="cu-tag badge"  v-else-if="messageCount>0 && messageCount<100">{{messageCount}}</view>
						<!-- <view class="cu-tag badge"  v-else></view> -->
					</view>
					消息
				</view>
				
				<view class="action" :class="footerTab=='4'?'text-purple':'text-gray'" @tap="clickTabPath('/pages/user/index')">
					<view class="cuIcon-my">
						<!-- <view class="cu-tag badge"></view> -->
					</view>
					我的
				</view>
				
			</view>
	</view>
</template>

<script>
	export default {
		name:"color-bar",
		props:{
			footerTab:{
				type: String,
				default: 0
			},
			messageCount:{
				type: Number,
				default: 0
			}
		},
		data() {
			return {
				
			};
		},
		methods: {
			clickTabPath(url) {
				const targetUrl = String(url || '').trim();
				if (!targetUrl) {
					return;
				}
				const targetRoute = targetUrl.replace(/^\//, '').split('?')[0];
				const pages = typeof getCurrentPages === 'function' ? getCurrentPages() : [];
				const currentPage = pages.length ? pages[pages.length - 1] : null;
				if (currentPage && currentPage.route === targetRoute) {
					return;
				}
				const targetIndex = pages.findIndex(page => page.route === targetRoute);
				if (targetIndex >= 0) {
					const delta = pages.length - 1 - targetIndex;
					if (delta > 0) {
						uni.navigateBack({ delta });
						return;
					}
				}
				const navigateMethod = pages.length >= 9 ? 'redirectTo' : 'navigateTo';
				uni[navigateMethod]({
					url: targetUrl
				});
			}
		}
	}
</script>

<style>

</style>

