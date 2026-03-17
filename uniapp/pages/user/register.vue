<template>
	<view class="content" :style="themeVarsStyle">
	  <cu-custom bgColor="bg-gradual-purple" :isBack="true"><block slot="backText">返回</block><block slot="content">注册</block></cu-custom>
		<block v-if="ismobile">
			<view class="">
				<view class="login-card">
					<view class="login-head"><image class="logoimg" :src="baseLogo" mode="widthFix"></image></view>
					<view class="login-input login-margin-b">
						<input type="number" v-model="username" placeholder="请输入手机号" />
					</view>
					<view class="login-input">
						<input type="number" :password="true" v-model="password" placeholder="请输入密码(6-16位)" />
					</view>
					<view class="margin-top flex justify-center">
						<button class="cu-btn bg-purple shadow-blur round" :loading="loading"   @tap="login">{{ loading ? "登录中...":"手机登录"}}</button>
					</view>
					<view class="flex justify-center">
						<view class="text-gray text-sm margin-top-xl"  @tap="wechatLogin">微信登录</view>
					</view>
				</view>
			</view>
			
		</block>
		<block   v-else>
			<!--  #ifndef  MP-WEIXIN	 -->
			<view class="login-bg">
				<view class="login-card">
					<view class="login-head"><image class="logoimg" :src="baseLogo" mode="widthFix"></image></view>
					<view style="text-align: center; color:#999;padding-bottom:10rpx">提示：演示站，验证码无需获取，可随机填写提交</view>
					<view class="login-input login-margin-b">
						<input type="text" :value="username" @input="userNameInput" placeholder="请输入用户名" />
					</view>
					<view class="login-input login-margin-b">
						<input type="number" :value="mobile" @input="mobileInput" placeholder="请输入手机号" />
						
					</view>
					<view class="login-input login-margin-b  flex justify-between">
						<input style="width: 80%;"  type="number" :value="code" @input="codeInput" placeholder="请输入手机验证码" />
						<view class="codeSend" v-if="sendSms"  @tap="getCaptcha">发送</view>
						<view class="text-purple codeSend" v-else >{{countDown}}</view>
					</view>
					<view class="login-input  login-margin-b">
						<input type="text" :password="true"  @input="passwordInput"  :value="password" placeholder="请输入密码(6-16位)" />
					</view>
					<!-- <view class="login-input">
						<input type="text" :password="true"  @input="password2Input"  :value="password2" placeholder="请再次输入密码(6-16位)" />
					</view> -->
					<view class="cu-bar btn-group margin-top">
						<button class="cu-btn bg-purple shadow-blur round" :loading="loading"   @tap="register">{{ loading ? "提交中...":"注 册"}}</button>
					</view>
					<view class="flex justify-center">
						<view class="text-gray text-sm margin-top-xl"  @tap="mobileLogin">手机登录</view>
					</view>
				</view>
			</view>
			<!--  #endif -->
			
			<!--  #ifdef  MP-WEIXIN	 -->
			<view class="logView">
				<button  @click="onGetUserProfile" class="logbt">
					<view class="login-head"><image class="logoimg" :src="baseLogo" mode="widthFix"></image></view>
					<view class="loginTitile"><text decode="true">
					请点击微信登录，并授权获取公开信息，
					登录后您将获得更多权益</text></view>
					<view class="cu-btn bg-purple shadow-blur round"><text class="cuIcon-lightauto"></text>微信登录</view>
					
				</button>
				<view class="text-gray text-sm margin-top-xl" @click="changMobileLogin()">手机登录</view>
				<!-- <button open-type="getUserInfo" @getuserinfo="onGotUserInfo" class="logbt">
					<image class="logoimg" src="../../static/images/user.png"></image>
					<view class="loginTitile"><text decode="true">
					请点击微信登录，并授权获取公开信息，
					登录士卓曼后您将获得更多权益</text></view>
					<view class="loginBtn">微信登录</view>
				</button> -->
			   <!-- <view class="mobileLogin" @click="changMobileLogin">手机登录</view> -->
			</view>
			<!--  #endif -->
		</block>
	</view>
</template>

<script>
	var _this;
	import {baseLogo} from '../../config/config.js';
	export default {
		data() {
			return {
				loading: false,
				mobile:'',
				username: "",
				password: "",
				password2:'',
				class_id:'',
				ismobile:false,
				group_id:1,
				code:'',
				baseLogo:baseLogo,
				countDown:60,
				sendSms:true,
				timer:null
				
			};
		},
		mounted() {
			_this = this;
		},
		onLoad(e) {
			
		},
		onShow() {
		},
		methods: {
			userNameInput(e){
				_this.username=e.detail.value;
			},
			mobileInput(e){
				_this.mobile=e.detail.value;
			},
			passwordInput(e){
				_this.password=e.detail.value;
			},
			password2Input(e){
				_this.password2=e.detail.value;
			},
			codeInput(e){
				_this.code=e.detail.value;
			},
			mobileLogin(){
				_this.$common.navigateTo('login');
			},
			getCaptcha() {
				//获取验证码
				if (!_this.$common.testString(this.mobile,'mobile')) {
					uni.showToast({
						icon: 'none',
						position: 'bottom',
						title: '手机号格式不正确'
					});
					return false;
				}else{ 
				_this.$api.sendSmsVerify(
					{
						event: 'register',
						mobile: _this.mobile
					},
					res => {
						if (res.code) {
							_this.$common.successToShow(res.msg);
							_this.sendSms=false;
							 _this.runCode();
						} else {
							_this.$common.errorToShow(res.msg);
							_this.sendSms=true;
						}
					}
				);
			 }
			},
			runCode(){
				this.timer =setInterval(function(){
					  if(!_this.sendSms){
						  if(_this.countDown>1){
								_this.countDown--; 
						  }else{
								_this.countDown=60;
								_this.sendSms=true;
								clearInterval(this.timer)
								return false;
												 
						  }
					  }				 				 
				},1000)
			},
			wxLogin(){
				wx.login({
				    success:(res) => {
				        this.code = res.code;
				    },
					fail: function (error) {
					        console.log('login failed ' + error);
					      }
				});
			},
			//切换微信登录
			wechatLogin(){
				_this.ismobile=false;
			},
			//切换手机登录
			changMobileLogin(){
				_this.ismobile=true;
				
			},
			register() {
				_this.loading = true;
				if (_this.username == '' || _this.username.length<3) {
					uni.showToast({
						icon: 'none',
						title: '请输入正确的用户名'
					});
					_this.loading = false;
					return;
				}
				if (!_this.$common.testString(this.mobile,'mobile')) {
					uni.showToast({
						icon: 'none',
						position: 'bottom',
						title: '手机号格式不正确'
					});
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
						//console.log(data);
						if (data.code == 1) {
							_this.loading = false;
							console.log(data);
							_this.$common.successToShow(data.msg,function(){
							  uni.navigateTo({
							  	url: '../index/index'
							  });
							});
							try {
								_this.$db.set('upload', 1)
								_this.$db.set('login', 1)
								_this.$db.set('token', data.data.user.token)
								_this.$db.set('user', data.data.user)	
								_this.$db.set('auth', data.data.auth)
								_this.refreshAppTheme(data.data.user)							
							} catch (e) {}
							
							
						}else{
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
			
			//改版后小程序登录规则
			//小程序登录
			onGetUserProfile() {
				// uni.showLoading({
				// 	title:"正在登录中..."
				// })
				var platform='wechat';
				var that=this;
				var fid=uni.getStorageSync('parentid')?uni.getStorageSync('parentid'):''; 
				uni.getUserProfile({
					 desc: '用于完善会员资料', // 声明获取用户个人信息后的用途，后续会展示在弹窗中，请谨慎填写
					success: res => {
						console.log(res)
						_this.$api.third(
							{
								code: _this.code,
								platform:platform,
								encrypted_data: res.encryptedData,
								iv: res.iv,
								raw_data: res.rawData,
								signature: res.signature
							},
							data => {
								console.log(data);
								//console.log(data.data.userinfo) 
								var res=data.data;
								if (data.code == 1) {
									this.$common.successToShow('登录成功!');
									try {
										this.$db.set('upload',1)
										this.$db.set('login', 1)
										this.$db.set('auth',res.auth)
										this.$db.set('user', res.userinfo)
										this.refreshAppTheme(res.userinfo)						
									} catch (e) {
										console.log("e: ",e);
									}
									uni.navigateTo({
										url: '../index/index'
									});
									
								}else{
									_this.wxLogin();
								}
							}
						);
					},
					fail: (res) => {
						console.log("res: ",res);
						_this.wxLogin();//重新获取登录code
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
				// uni.login({
				// 	success: loginRes => {
				// 		uni.hideLoading();
				// 		console.log('第一次登录'+loginRes.code)
				// 		if (loginRes.code && loginRes.code!='') {
				// 			console.log('2222222222222222222')
							
				// 		}
				// 	}
				// })
			}
		}
	}
</script>

<style>
	page{ background: #fff;}
	.content{ height: 100%;}
	.logView{
		display: flex;
		align-items: center;
		justify-content:center ;
		flex-direction:column;
		align-items: center;     /* 垂直居中 */
		 width: 100%;
		 position: fixed;
		 left: 50%;
		 top: 50%;
		 transform: translate(-50%,-50%);
	}
	.logbt {
		display: flex;
		align-items: center;
		justify-content:center ;
		flex-direction:column;
		align-items: center;     /* 垂直居中 */
		 width: 100%;
		background: none;
		border: none !important; 	
	}

	.logbt:after {
		border: none !important;
	}
   .logbt .logoimg{
	   width: 200rpx;
	   height: 200rpx;
	   display: block;
   }
   .logbt .wechatimg{
   	  width: 150rpx;
   	  height: 150rpx;
   	   display: block;
   }
   .loginTitile{ padding: 50rpx; font-size: 28rpx; color: #787878; line-height: 1.3; text-align: center;} 
   .loginBtn{ width: 300rpx; height: 70rpx; line-height: 70rpx; color: #fff; background:#2562a1; border-radius: 10rpx; border: none;}
	image {
		width: 100rpx;
		height: 100rpx;
	}
.mobileLogin{ background: none; color: #999; text-align: center; margin: 40rpx auto; border: none; font-size: 26rpx;}
	.landing[type=primary] {
		height: 84rpx;
		line-height: 84rpx;
		border-radius: 44rpx;
		font-size: 32rpx;
		/* background: linear-gradient(left, #86B5F4, #4790EF); */
		background-color: #ffbc32;
	}

	.login-btn {
		padding: 10rpx 20rpx;
		margin-top: 60rpx;
	}

	.login-function {
		overflow: auto;
		padding: 20rpx 20rpx 30rpx 20rpx;
	}

	.login-forget {
		float: left;
		font-size: 26rpx;
		color: #999;
	}

	.login-register {
		color: #666;
		float: right;
		font-size: 26rpx;

	}

	.login-input input {
		background: #F2F5F6;
		font-size: 28rpx;
		padding: 10rpx 25rpx;
		height: 80rpx;
		line-height: 80rpx;
		border-radius: 40rpx;
	}

	.login-margin-b {
		margin-bottom: 25rpx;
	}

	.login-input {
		padding: 10rpx 20rpx;
	}

	.login-head {
		font-size: 34rpx;
		text-align: center;
		padding: 25rpx 10rpx 55rpx 10rpx;
	}
.login-head image{ width: 200rpx;}
	.login-card {
		background: #fff;
		border-radius: 12rpx;
		padding: 10rpx 25rpx;
		position: relative;
		margin-top: 120rpx;
	}

	.login-bg {
		height: 100%;
		padding: 25rpx;
	}
	.codeSend{
		line-height:80rpx; height: 80rpx; width: 100rpx ; text-align: center;
		background: #f7f7f7; color: #666; border-radius:20rpx;
	}
</style>



