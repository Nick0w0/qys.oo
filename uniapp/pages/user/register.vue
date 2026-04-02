<template>
	<view class="content" :style="themeVarsStyle">
		<view class="login-topbar">
			<view class="login-topbar__back" @tap="goBack">
				<text class="cuIcon-back"></text>
			</view>
			<view class="login-topbar__title">注册</view>
			<view class="login-topbar__placeholder"></view>
		</view>

		<block v-if="ismobile">
			<view class="login-bg login-bg--mobile">
				<view class="login-card">
					<view class="register-hint">提示：演示站，验证码无需获取，可随机填写提交</view>
					<view class="login-input login-margin-b">
						<input type="text" :value="username" @input="userNameInput" placeholder="请输入用户名" />
					</view>
					<view class="login-input login-margin-b">
						<input type="number" :value="mobile" @input="mobileInput" placeholder="请输入手机号" />
					</view>
					<view class="code-row login-margin-b">
						<view class="login-input code-row__field">
							<input type="number" :value="code" @input="codeInput" placeholder="请输入手机验证码" />
						</view>
						<view class="codeSend" v-if="sendSms" @tap="getCaptcha">发送</view>
						<view class="codeSend codeSend--disabled" v-else>{{ countDown }}s</view>
					</view>
					<view class="login-input login-margin-b">
						<input type="text" :password="true" @input="passwordInput" :value="password" placeholder="请输入密码(6-16位)" />
					</view>
					<view class="login-action">
						<button class="cu-btn round login-btn-primary" :loading="loading" @tap="register">{{ loading ? "提交中..." : "注册" }}</button>
					</view>
					<view class="flex justify-center">
						<view class="login-link" @tap="mobileLogin">手机登录</view>
					</view>
				</view>
			</view>
		</block>

		<block v-else>
			<!--  #ifndef  MP-WEIXIN	 -->
			<view class="login-bg">
				<view class="login-card">
					<view class="register-hint">提示：演示站，验证码无需获取，可随机填写提交</view>
					<view class="login-input login-margin-b">
						<input type="text" :value="username" @input="userNameInput" placeholder="请输入用户名" />
					</view>
					<view class="login-input login-margin-b">
						<input type="number" :value="mobile" @input="mobileInput" placeholder="请输入手机号" />
					</view>
					<view class="code-row login-margin-b">
						<view class="login-input code-row__field">
							<input type="number" :value="code" @input="codeInput" placeholder="请输入手机验证码" />
						</view>
						<view class="codeSend" v-if="sendSms" @tap="getCaptcha">发送</view>
						<view class="codeSend codeSend--disabled" v-else>{{ countDown }}s</view>
					</view>
					<view class="login-input login-margin-b">
						<input type="text" :password="true" @input="passwordInput" :value="password" placeholder="请输入密码(6-16位)" />
					</view>
					<view class="login-action">
						<button class="cu-btn round login-btn-primary" :loading="loading" @tap="register">{{ loading ? "提交中..." : "注册" }}</button>
					</view>
					<view class="flex justify-center">
						<view class="login-link" @tap="mobileLogin">手机登录</view>
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
					<view class="login-link" @click="changMobileLogin()">手机号注册</view>
				</view>
			</view>
			<!--  #endif -->
		</block>
	</view>
</template>

<script>
	import { hasBoundSchool } from '../../config/wechat-auth.js';
	var _this;

	export default {
		data() {
			return {
				loading: false,
				mobile: '',
				username: "",
				password: "",
				password2: '',
				class_id: '',
				ismobile: true,
				group_id: 1,
				code: '',
				wxCode: '',
				countDown: 60,
				sendSms: true,
				timer: null
			};
		},
		mounted() {
			_this = this;
		},
		onLoad(e) {

		},
		onShow() {
			// #ifdef MP-WEIXIN
			if (!this.ismobile) {
				this.wxLogin();
			}
			// #endif
		},
		methods: {
			redirectAfterLogin(url) {
				uni.reLaunch({
					url: url
				});
			},
			getPostLoginUrl(userInfo) {
				return hasBoundSchool(userInfo) ? '/pages/index/index' : '/pages/plugin/index';
			},
			handleLoginSuccess(userInfo, auth, message) {
				if (!userInfo) {
					return;
				}
				try {
					this.$db.set('upload', 1);
					this.$db.set('login', 1);
					this.$db.set('token', userInfo.token || '');
					this.$db.set('user', userInfo);
					this.$db.set('auth', auth || {});
					this.refreshAppTheme(userInfo);
				} catch (e) {}
				const targetUrl = this.getPostLoginUrl(userInfo);
				this.$common.successToShow(message || '操作成功', () => {
					this.redirectAfterLogin(targetUrl);
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
					url: '/pages/user/login'
				});
			},
			userNameInput(e) {
				_this.username = e.detail.value;
			},
			mobileInput(e) {
				_this.mobile = e.detail.value;
			},
			passwordInput(e) {
				_this.password = e.detail.value;
			},
			password2Input(e) {
				_this.password2 = e.detail.value;
			},
			codeInput(e) {
				_this.code = e.detail.value;
			},
			mobileLogin() {
				_this.$common.navigateTo('login');
			},
			getCaptcha() {
				if (!_this.$common.testString(this.mobile, 'mobile')) {
					uni.showToast({
						icon: 'none',
						position: 'bottom',
						title: '手机号格式不正确'
					});
					return false;
				} else {
					_this.$api.sendSmsVerify(
						{
							event: 'register',
							mobile: _this.mobile
						},
						res => {
							if (res.code) {
								_this.$common.successToShow(res.msg);
								_this.sendSms = false;
								_this.runCode();
							} else {
								_this.$common.errorToShow(res.msg);
								_this.sendSms = true;
							}
						}
					);
				}
			},
			runCode() {
				this.timer = setInterval(function() {
					if (!_this.sendSms) {
						if (_this.countDown > 1) {
							_this.countDown--;
						} else {
							_this.countDown = 60;
							_this.sendSms = true;
							clearInterval(_this.timer)
							return false;
						}
					}
				}, 1000)
			},
			wxLogin() {
				wx.login({
					success: (res) => {
						this.wxCode = res.code;
					},
					fail: function(error) {
						console.log('login failed ' + error);
					}
				});
			},
			wechatLogin() {
				_this.ismobile = false;
				_this.wxLogin();
			},
			changMobileLogin() {
				_this.ismobile = true;
			},
			register() {
				_this.loading = true;
				if (_this.username == '' || _this.username.length < 3) {
					uni.showToast({
						icon: 'none',
						title: '请输入正确的用户名'
					});
					_this.loading = false;
					return;
				}
				if (!_this.$common.testString(this.mobile, 'mobile')) {
					uni.showToast({
						icon: 'none',
						position: 'bottom',
						title: '手机号格式不正确'
					});
					_this.loading = false;
					return false;
				}
				if (_this.code == '') {
					uni.showToast({
						icon: 'none',
						title: '请输入验证码'
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

				_this.$api.register(
					{
						username: _this.username,
						mobile: _this.mobile,
						password: _this.password,
						code: _this.code,
					},
					data => {
						if (data.code == 1) {
							_this.loading = false;
							console.log(data);
							_this.handleLoginSuccess(data.data.user, data.data.auth, data.msg);
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
			onGetUserProfile() {
				var platform = 'wechat';
				var that = this;
				var fid = uni.getStorageSync('parentid') ? uni.getStorageSync('parentid') : '';
				uni.getUserProfile({
					desc: '用于完善会员资料',
					success: res => {
						console.log(res)
						_this.$api.third(
							{
								code: _this.wxCode,
								platform: platform,
								encrypted_data: res.encryptedData,
								iv: res.iv,
								raw_data: res.rawData,
								signature: res.signature
							},
							data => {
								console.log(data);
								var res = data.data;
								if (data.code == 1) {
									this.handleLoginSuccess(res.userinfo, res.auth, '登录成功!');
								} else {
									_this.wxLogin();
								}
							}
						);
					},
					fail: (res) => {
						console.log("res: ", res);
						_this.wxLogin();
						uni.hideLoading()
						if (res.errMsg == "getUserInfo:cancel" || res.errMsg == "getUserInfo:fail auth deny") {
							uni.showModal({
								title: '用户授权失败',
								showCancel: false,
								content: '请点击重新授权，如果未弹出授权，请尝试长按删除小程序，重新进入!',
								success: function(res) {
									if (res.confirm) {
										console.log('用户点击确定')
										uni.navigateBack()
									}
								}
							})
						}
					}
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
		background-image:
			linear-gradient(180deg, rgba(255,255,255,0.12) 0%, rgba(255,248,235,0.24) 48%, rgba(120,203,201,0.22) 100%),
			url('../../static/images/login-cover.png');
		background-size: cover;
		background-position: center top;
		background-repeat: no-repeat;
	}

	.login-topbar {
		position: absolute;
		top: 0;
		left: 0;
		right: 0;
		display: flex;
		align-items: center;
		justify-content: space-between;
		padding: calc(env(safe-area-inset-top) + 16rpx) 24rpx 0;
		box-sizing: border-box;
		z-index: 2;
	}

	.login-topbar__back,
	.login-topbar__placeholder {
		width: 72rpx;
		height: 72rpx;
		flex-shrink: 0;
	}

	.login-topbar__back {
		display: flex;
		align-items: center;
		justify-content: center;
		border-radius: 50%;
		background: rgba(255,255,255,0.18);
		backdrop-filter: blur(10rpx);
		-webkit-backdrop-filter: blur(10rpx);
		color: #ffffff;
		font-size: 34rpx;
	}

	.login-topbar__title {
		font-size: 38rpx;
		line-height: 1;
		font-weight: 500;
		letter-spacing: 2rpx;
		color: #ffffff;
		text-shadow: 0 4rpx 12rpx rgba(68, 94, 96, 0.2);
	}

	.login-bg,
	.logView {
		min-height: 100vh;
		padding: calc(env(safe-area-inset-top) + 164rpx) 32rpx 88rpx;
		box-sizing: border-box;
		display: flex;
		justify-content: center;
		align-items: center;
	}

	.login-bg--mobile {
		padding-top: calc(env(safe-area-inset-top) + 176rpx);
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

	.register-hint {
		margin-bottom: 26rpx;
		font-size: 24rpx;
		line-height: 1.6;
		color: rgba(92, 111, 115, 0.84);
		text-align: center;
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

	.code-row {
		display: flex;
		align-items: center;
		gap: 16rpx;
	}

	.code-row__field {
		flex: 1;
		min-width: 0;
	}

	.codeSend {
		width: 132rpx;
		height: 88rpx;
		line-height: 88rpx;
		text-align: center;
		font-size: 26rpx;
		color: #5d6d71;
		background: rgba(248,249,250,0.9);
		border: 1rpx solid rgba(255,255,255,0.55);
		border-radius: 44rpx;
		flex-shrink: 0;
	}

	.codeSend--disabled {
		color: #7a4fff;
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
