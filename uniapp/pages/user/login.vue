<template>
	<view class="content" :style="themeVarsStyle">
		<image class="login-cover-image" src="../../static/images/login-cover.png" mode="aspectFill"></image>
		<view class="login-cover-mask"></view>

		<block v-if="ismobile">
			<view class="login-bg login-bg--mobile">
				<view class="login-card">
					<view class="login-input login-margin-b">
						<input type="number" v-model="username" placeholder="请输入手机号" />
					</view>
					<view class="login-input">
						<input type="number" :password="true" v-model="password" placeholder="请输入密码(6-16位)" />
					</view>
					<view class="login-action">
						<button class="cu-btn round login-btn-primary" :loading="loading" @tap="login">{{ loading ? "登录中..." : "手机登录" }}</button>
					</view>
					<view class="flex justify-center">
						<view class="login-link" @tap="wechatLogin">微信登录</view>
					</view>
				</view>
			</view>
		</block>

		<block v-else>
			<!--  #ifndef  MP-WEIXIN	 -->
			<view class="login-bg">
				<view class="login-card">
					<view class="login-input login-margin-b">
						<input type="number" v-model="username" placeholder="请输入手机号" />
					</view>
					<view class="login-input">
						<input type="password" :password="true" v-model="password" placeholder="请输入密码(6-16位)" />
					</view>
					<view class="login-action">
						<button class="cu-btn round login-btn-primary" :loading="loading" @tap="login">{{ loading ? "登录中..." : "手机登录" }}</button>
					</view>
					<view class="flex justify-center">
						<view class="login-link" @tap="register">注册新账户</view>
					</view>
				</view>
			</view>
			<!--  #endif -->

			<!--  #ifdef  MP-WEIXIN	 -->
			<view class="logView">
				<view class="login-card login-card--wechat">
					<button @click="onGetUserProfile" class="logbt">
						<view class="loginTitile">
							<text decode="true">请点击微信登录，并授权获取公开信息，登录后您将获得更多权益</text>
						</view>
						<view class="cu-btn round login-btn-primary login-btn-primary--wechat">
							<text class="cuIcon-lightauto"></text>
							微信登录
						</view>
					</button>
					<view class="login-link-row">
						<view class="login-link" @click="changMobileLogin()">手机登录</view>
						<view class="login-link" @tap="register">注册账号</view>
					</view>
				</view>
			</view>
			<!--  #endif -->
		</block>
	</view>
</template>

<script>
	var _this;
	import { hasBoundSchool, refreshWechatCode, getWechatReadyState, requestWechatLogin } from '../../config/wechat-auth.js';

	export default {
		data() {
			return {
				loading: false,
				user: [],
				username: "",
				password: "",
				class_id: '',
				ismobile: false,
				group_id: 1,
				code: ''
			};
		},
		mounted() {
			_this = this;
		},
		onLoad(e) {

		},
		onShow() {
			this.user = this.$common.userInfo();
			if (typeof(this.user) == "undefined" || this.user == '' || this.user == null) {
				// #ifdef MP-WEIXIN
				this.wxLogin();
				// #endif
				return;
			}
			this.routeAfterLoginSuccess(this.user);
		},
		methods: {
			redirectAfterLogin(url) {
				uni.reLaunch({
					url: url
				});
			},
			goBack() {
				const pages = getCurrentPages();
				if (pages.length > 1) {
					uni.navigateBack({
						delta: 1
					});
					return;
				}
				uni.reLaunch({
					url: '/pages/plugin/gate'
				});
			},
			getPostLoginUrl(userInfo) {
				return hasBoundSchool(userInfo) ? '/pages/index/index' : '/pages/plugin/index';
			},
			routeAfterLoginSuccess(userInfo) {
				this.user = userInfo || this.$db.get('user') || null;
				if (!this.user || !this.user.id) {
					return;
				}
				this.refreshAppTheme(this.user);
				const targetUrl = this.getPostLoginUrl(this.user);
				this.redirectAfterLogin(targetUrl);
			},
			showWechatDeniedModal() {
				uni.showModal({
					title: '用户授权失败',
					showCancel: false,
					content: '请重新授权，或先使用手机号登录',
					success: () => {
						this.changMobileLogin();
					}
				})
			},
			wxLogin() {
				refreshWechatCode((success, code) => {
					this.code = success ? code : '';
					if (!success) {
						console.log('login failed');
					}
				});
			},
			wechatLogin() {
				_this.ismobile = false;
			},
			changMobileLogin() {
				_this.ismobile = true;
			},
			register() {
				this.$common.navigateTo('register');
			},
			login() {
				_this.loading = true;
				if (_this.username == '' || _this.username.length < 11) {
					uni.showToast({
						icon: 'none',
						title: '请输入正确的手机号'
					});
					_this.loading = false;
					return;
				}
				if (_this.password == '') {
					uni.showToast({
						icon: 'none',
						title: '请输入密码'
					});
					_this.loading = false;
					return;
				}
				_this.$api.login(
					{
						account: _this.username,
						password: _this.password,
					},
					data => {
						if (data.code == 1) {
							_this.loading = false;
							try {
								_this.$db.set('upload', 1)
								_this.$db.set('login', 1)
								_this.$db.set('token', data.data.userinfo.token)
								_this.$db.set('user', data.data.userinfo)
								_this.$db.set('auth', data.data.auth)
								_this.refreshAppTheme(data.data.userinfo)
							} catch (e) {}
							_this.$common.successToShow(data.msg, function() {
								_this.routeAfterLoginSuccess(data.data.userinfo);
							});
						} else {
							_this.loading = false;
							uni.showToast({
								duration: 1500,
								icon: 'none',
								title: data.msg
							});
						}
					}
				)
			},
			onGotUserInfo() {
				this.onGetUserProfile();
			},
			onGetUserProfile() {
				const readyState = getWechatReadyState();
				if (!readyState.ok) {
					uni.showToast({
						icon: 'none',
						title: readyState.message
					});
					this.changMobileLogin();
					return;
				}
				if (!_this.code) {
					_this.wxLogin();
					uni.showToast({
						icon: 'none',
						title: '微信登录初始化中，请再试一次'
					});
					return;
				}
				requestWechatLogin(this, _this.code, (result) => {
					if (result.needRefreshCode) {
						_this.wxLogin();
					}
					if (!result.ok) {
						if (result.canceled) {
							this.showWechatDeniedModal();
							return;
						}
						uni.showToast({
							icon: 'none',
							title: result.message || '微信登录失败'
						});
						return;
					}
					this.$common.successToShow('登录成功!', () => {
						this.routeAfterLoginSuccess(result.userinfo || null);
					});
				})
			}
		}
	}
</script>

<style>
	page {
		background: #78cbc9;
	}

	.content {
		min-height: 100vh;
		position: relative;
		overflow: hidden;
	}

	.login-cover-image,
	.login-cover-mask {
		position: absolute;
		left: 0;
		top: 0;
		right: 0;
		bottom: 0;
	}

	.login-cover-image {
		width: 100%;
		height: 100%;
	}

	.login-cover-mask {
		background: linear-gradient(180deg, rgba(255,255,255,0.10) 0%, rgba(255,248,235,0.18) 48%, rgba(120,203,201,0.16) 100%);
	}

	.login-bg,
	.logView {
		min-height: 100vh;
		padding: calc(env(safe-area-inset-top) + 104rpx) 32rpx 88rpx;
		box-sizing: border-box;
		display: flex;
		justify-content: center;
		align-items: center;
		position: relative;
		z-index: 2;
	}

	.login-bg--mobile {
		padding-top: calc(env(safe-area-inset-top) + 116rpx);
	}

	.login-card {
		width: 100%;
		max-width: 580rpx;
		padding: 42rpx 30rpx 34rpx;
		background: rgba(255,255,255,0.86);
		border: 1rpx solid rgba(255,255,255,0.36);
		border-radius: 32rpx;
		box-shadow: 0 18rpx 48rpx rgba(81, 116, 120, 0.18);
		backdrop-filter: blur(12rpx);
		-webkit-backdrop-filter: blur(12rpx);
	}

	.login-card--wechat {
		padding-top: 52rpx;
	}

	.login-input {
		padding: 0;
	}

	.login-input input {
		height: 88rpx;
		line-height: 88rpx;
		padding: 0 28rpx;
		font-size: 28rpx;
		color: #415559;
		background: rgba(244,248,248,0.9);
		border: 1rpx solid rgba(255,255,255,0.55);
		border-radius: 44rpx;
		box-shadow: inset 0 2rpx 8rpx rgba(86, 111, 115, 0.06);
	}

	.login-margin-b {
		margin-bottom: 24rpx;
	}

	.login-action {
		margin-top: 36rpx;
	}

	.login-btn-primary {
		width: 100%;
		height: 88rpx;
		line-height: 88rpx;
		font-size: 28rpx;
		font-weight: 500;
		letter-spacing: 2rpx;
		color: #fff;
		background: linear-gradient(135deg, rgba(122, 79, 255, 0.9) 0%, rgba(79, 114, 255, 0.88) 100%);
		border: 1rpx solid rgba(255,255,255,0.32);
		box-shadow: 0 18rpx 38rpx rgba(87, 103, 208, 0.22);
		backdrop-filter: blur(8rpx);
		-webkit-backdrop-filter: blur(8rpx);
	}

	.login-btn-primary::after,
	.logbt::after {
		border: none;
	}

	.login-btn-primary--wechat {
		display: flex;
		align-items: center;
		justify-content: center;
	}

	.login-btn-primary .cuIcon-lightauto {
		margin-right: 10rpx;
		font-size: 30rpx;
	}

	.login-link {
		margin-top: 28rpx;
		font-size: 24rpx;
		letter-spacing: 1rpx;
		color: #5c6f73;
	}

	.login-link-row {
		display: flex;
		align-items: center;
		justify-content: center;
		column-gap: 36rpx;
	}

	.logbt {
		width: 100%;
		padding: 0;
		background: none;
		border: none !important;
		display: flex;
		flex-direction: column;
		align-items: center;
		justify-content: center;
	}

	.loginTitile {
		padding: 20rpx 18rpx 44rpx;
		font-size: 26rpx;
		line-height: 1.5;
		text-align: center;
		color: rgba(92, 111, 115, 0.82);
	}

	image {
		width: 100rpx;
		height: 100rpx;
	}
</style>
