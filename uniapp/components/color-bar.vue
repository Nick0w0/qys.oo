<template>
		<view :style="themeVarsStyle">  
			<view style="height:70px"></view>
			<view class="app-tabbar">
				
				<view class="app-tabbar-item" :class="footerTab=='0' ? 'is-active' : 'is-inactive'" @tap="clickTabPath('/pages/index/index')"> 
					<view class="app-tabbar-item-inner">
						<view class="cuIcon-homefill app-tabbar-icon"></view>
						<view class="app-tabbar-label"><text class="app-tabbar-label-text">首页</text></view>
					</view>
				</view>
				
				<view class="app-tabbar-item" :class="footerTab=='1' ? 'is-active' : 'is-inactive'" @tap="clickTabPath('/pages/index/hot')">
					<view class="app-tabbar-item-inner">
						<view class="cuIcon-hotfill app-tabbar-icon"></view>
						<view class="app-tabbar-label"><text class="app-tabbar-label-text">动态</text></view>
					</view>
				</view>
				
				<view class="app-tabbar-item add-action" :class="footerTab=='2' ? 'is-active' : 'is-inactive'" @tap="clickTabPath('/pages/index/publish')">
					<view class="app-tabbar-item-inner">
						<button class="cu-btn cuIcon-add bg-gradual-purple shadow"></button>
						<view class="app-tabbar-label"><text class="app-tabbar-label-text">发布</text></view>
					</view>
				</view>
				
				<view class="app-tabbar-item" :class="footerTab=='3' ? 'is-active' : 'is-inactive'" @tap="clickTabPath('/pages/user/message')">
					<view class="app-tabbar-item-inner">
						<view class="cuIcon-notice app-tabbar-icon">
							 <view class="cu-tag badge"  v-if="messageCount>99">99+</view>
							<view class="cu-tag badge"  v-else-if="messageCount>0 && messageCount<100">{{messageCount}}</view>
							<!-- <view class="cu-tag badge"  v-else></view> -->
						</view>
						<view class="app-tabbar-label"><text class="app-tabbar-label-text">消息</text></view>
					</view>
				</view>
				
				<view class="app-tabbar-item" :class="footerTab=='4' ? 'is-active' : 'is-inactive'" @tap="clickTabPath('/pages/user/index')">
					<view class="app-tabbar-item-inner">
						<view class="cuIcon-my app-tabbar-icon">
							<!-- <view class="cu-tag badge"></view> -->
						</view>
						<view class="app-tabbar-label"><text class="app-tabbar-label-text">我的</text></view>
					</view>
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
				const rootTabRoutes = [
					'pages/index/index',
					'pages/index/hot',
					'pages/user/message',
					'pages/user/index'
				];
				const isRootTabRoute = rootTabRoutes.indexOf(targetRoute) !== -1;
				const pages = typeof getCurrentPages === 'function' ? getCurrentPages() : [];
				const currentPage = pages.length ? pages[pages.length - 1] : null;
				if (currentPage && currentPage.route === targetRoute) {
					return;
				}
				if (isRootTabRoute) {
					uni.reLaunch({
						url: targetUrl
					});
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
	.app-tabbar{
		position: fixed;
		left: 0;
		right: 0;
		bottom: 0;
		z-index: 999;
		display: flex;
		align-items: flex-start;
		justify-content: space-between;
		background: #ffffff;
		box-shadow: 0 -1rpx 10rpx rgba(15, 23, 42, 0.08);
		height: 96rpx;
		overflow: visible;
		padding: 0 8rpx max(env(safe-area-inset-bottom), 0rpx);
		box-sizing: border-box;
	}
	.app-tabbar-item{
		flex: 1;
		height: 96rpx;
		display:flex;
		align-items:center;
		justify-content:center;
		font-size: 0;
		line-height: 1;
		color: #b7b7b7;
	}
	.app-tabbar-item.is-active{
		color: #8da9ff;
	}
	.app-tabbar-item.is-inactive{
		color: #b7b7b7;
	}
	.app-tabbar-item.add-action{
		position: relative;
		top: -22rpx;
	}
	.app-tabbar-item-inner{
		display:flex;
		flex-direction:column;
		align-items:center;
		justify-content:flex-start;
		height: 100%;
		padding-top: 10rpx;
		padding-bottom: max(env(safe-area-inset-bottom), 0rpx);
		box-sizing: border-box;
	}
	.app-tabbar-icon{
		position: relative;
		font-size: 46rpx;
		line-height: 1;
		height: 46rpx;
		display:flex;
		align-items:center;
		justify-content:center;
	}
	.app-tabbar-icon .badge{
		position: absolute;
		top: -12rpx;
		right: -22rpx;
		min-width: 30rpx;
		height: 30rpx;
		padding: 0 8rpx;
		border-radius: 999rpx;
		display: inline-flex;
		align-items: center;
		justify-content: center;
		background: #ef4444;
		color: #ffffff;
		font-size: 18rpx;
		line-height: 1;
		box-sizing: border-box;
	}
	.app-tabbar .app-tabbar-item .app-tabbar-label{
		display: flex;
		align-items: center;
		justify-content: center;
		width: 84rpx;
		margin-top: 8rpx;
		height: 16rpx;
		text-align: center;
		white-space: nowrap;
	}
	.app-tabbar .app-tabbar-item .app-tabbar-label-text{
		display: inline-block;
		font-size: 12rpx;
		line-height: 16rpx;
		letter-spacing: 1rpx;
		transform: scale(0.82);
		transform-origin: center center;
	}
	.app-tabbar .add-action .app-tabbar-label{
		margin-top: 0;
		transform: translateY(-8rpx);
	}
	.app-tabbar .add-action .app-tabbar-item-inner{
		justify-content:flex-start;
		padding-top: 0;
	}
	.app-tabbar .add-action .cu-btn{
		width: 92rpx;
		height: 92rpx;
		min-height: 92rpx;
		padding: 0;
		border-radius: 50%;
		font-size: 46rpx;
		line-height: 92rpx;
		transform: translateY(-20rpx);
		box-shadow: 0 10rpx 26rpx rgba(141, 169, 255, 0.32);
	}
</style>

