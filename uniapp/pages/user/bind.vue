<template>
	<view class="content">
	<block v-if="ismobile">
		<view class="login-bg">
			<view class="login-card">
				<view class="login-head"><image class="logoimg" src="../../static/images/search.png" mode="widthFix"></image></view>
				<view class="login-input login-margin-b">
					<input type="number" v-model="username" placeholder="请输入手机号" />
				</view>
				<view class="login-input">
					<input type="number" :password="true" v-model="password" placeholder="请输入密码(6-16位)" />
				</view>
				<view class="login-btn">
					<button class="loginBtn" :loading="loading"   @tap="login">{{ loading ? "登录中...":"登 录"}}</button>
					<view class="login-function">
						<view class="mobileLogin" @tap="wechatLogin">微信登录</view>
					</view>
				</view>
			</view>
		</view>
		
	</block>
		<view style="margin-top:8px;text-align: center;" v-else>
			<!--  #ifndef  MP-WEIXIN	 -->
			<view class="login-bg">
				<view class="login-card">
					<view class="login-head"><image class="logoimg" src="../../static/images/search.png" mode="widthFix"></image></view>
					<view class="login-input login-margin-b">
						<input type="number" v-model="username" placeholder="请输入手机号" />
					</view>
					<view class="login-input">
						<input type="number" :password="true" v-model="password" placeholder="请输入密码(6-16位)" />
					</view>
					<view class="login-btn">
						<button class="loginBtn" :loading="loading"   @tap="login">{{ loading ? "登录中...":"登 录"}}</button>
					</view>
				</view>
			</view>
			<!--  #endif -->

			<!--  #ifdef  MP-WEIXIN	 -->
			<view class="logView">
				<button  @click="onGetUserProfile" class="logbt">
					<image class="logoimg" src="../../static/images/user.png"></image>
					<view class="loginTitile"><text decode="true">
					请点击微信登录，并授权获取公开信息，
					登录知牙后您将获得更多权益</text></view>
					<view class="loginBtn">微信登录</view>
				</button>
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
		</view>

	</view>
</template>

<script>
	var _this;
	export default {
		data() {
			return {
				loading: false,
				username: "",
				password: "",
				class_id:'',
				ismobile:false,
				group_id:1,
				code:''
				
			};
		},
		onLoad(e) {
			if(e.group_id){
				_that.group_id=e.group_id?e.group_id:1;	
			}	
			if (e.token) {
				uni.showLoading()
				//console.log(e.token);
				uni.setStorageSync('upload', 1)
				uni.setStorageSync('login', 1)
				uni.setStorageSync('token', e.token)
				uni.showToast({
					duration: 1000,
					title: '登录成功'
				});
				setTimeout((e => { 
					uni.hideLoading()
					uni.switchTab({
						url: '../user/user'
					});
				}), 1000);
			}
		},
		onShow() {
			this.wxLogin();
		},
		mounted() {
			_this = this;
		},
		methods: {
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
			login() {
				_this.loading = true;
				if (_this.username == '' || _this.username.length<11) {
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
				_this.$api({
					url: 'user/login',
					data: {
						account: _this.username,
						password: _this.password,
						//device_type: api.DeviceType
					},
					success: data => {
						//console.log(data);
						if (data.code == 1) {
							_this.loading = false;
							//console.log(data);
							uni.showToast({
								duration: 500,
								title: data.msg
							});
							try {
								uni.setStorageSync('upload', 1)
								uni.setStorageSync('login', 1)
								uni.setStorageSync('token', data.data.userinfo.token)
								uni.setStorageSync('user', data.data.userinfo)						
							} catch (e) {}
							//扫码进来的，如果是老师，userType=1，附带class_id
							if(data.data.userinfo.group_id==0 && _this.group_id!=''){
								uni.navigateTo({
									url: '../user/bind?group_id='+that.group_id
								});
							}else{
								setTimeout(function() {
									uni.navigateBack()													
								}, 500)
							}
							
						}else{
							_this.loading = false;
							uni.showToast({
								duration: 1500,
								icon: 'none',
								title: data.msg
							});
						}
						
					}
				})
			},
			//小程序登录（微信登录）
			onGotUserInfo() {
				this.$common.errorToShow('正在登录中...');
				var platform='wechat';
				uni.login({
					success: loginRes => {
						uni.hideLoading();
						console.log('第一次登录'+loginRes.code)
						if (loginRes.code) {
							uni.getUserInfo({
								withCredentials: true,
								success: res => {
									console.log('用户信息'+loginRes.code+res.encryptedData+res.iv+res.rawData+res.signature)
									_this.$api.third(
										{
											code: loginRes.code,
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
												} catch (e) {
													console.log("e: ",e);
												}
												//扫码进来的，如果是员工认证，group_id=2，如果是认证普通用户group=1
												if(data.data.userinfo.group_id==0){
													uni.navigateTo({
														url: '../user/index?group_id='+_this.group_id
													});
												}else{
													uni.switchTab({
														url: '../index/index'
													});
												}
												
											}
										}
									);
								},
								fail: (res) => {
									if (res.errMsg == "getUserInfo:cancel" || res.errMsg == "getUserInfo:fail auth deny") {
										uni.showModal({
											title: '用户授权失败',
											showCancel: false,
											content: '请点击重新授权，如果未弹出授权，请尝试长按删除小程序，重新进入!',
											success: function(res) {
												if (res.confirm) {
													console.log('用户点击确定')
												}
											}
										})
									}

								}
							})
						}
					}
				})
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
									} catch (e) {
										console.log("e: ",e);
									}
									//扫码进来的，如果是员工认证，group_id=2，如果是认证普通用户group=1
									if(res.userinfo.group_id==0){
										console.log("res.userinfo.group_id: ",res.userinfo.group_id);
										uni.switchTab({
											url: '../user/index?group_id='+_this.group_id
										});
									}else{
										console.log("111: ",111);
										uni.switchTab({
											url: '../index/index'
										});
									}
									
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
	   width: 300upx;
	   height: 300upx;
	   display: block;
   }
   .logbt .wechatimg{
   	  width: 150upx;
   	  height: 150upx;
   	   display: block;
   }
   .loginTitile{ padding: 50upx; font-size: 28upx; color: #787878; line-height: 1.3; text-align: center;} 
   .loginBtn{ width: 300upx; height: 70upx; line-height: 70upx; color: #fff; background:#2562a1; border-radius: 10upx; border: none;}
	image {
		width: 100upx;
		height: 100upx;
	}
.mobileLogin{ background: none; color: #999; text-align: center; margin: 40upx auto; border: none; font-size: 26upx;}
	.landing[type=primary] {
		height: 84upx;
		line-height: 84upx;
		border-radius: 44upx;
		font-size: 32upx;
		/* background: linear-gradient(left, #86B5F4, #4790EF); */
		background-color: #ffbc32;
	}

	.login-btn {
		padding: 10upx 20upx;
		margin-top: 60upx;
	}

	.login-function {
		overflow: auto;
		padding: 20upx 20upx 30upx 20upx;
	}

	.login-forget {
		float: left;
		font-size: 26upx;
		color: #999;
	}

	.login-register {
		color: #666;
		float: right;
		font-size: 26upx;

	}

	.login-input input {
		background: #F2F5F6;
		font-size: 28upx;
		padding: 10upx 25upx;
		height: 62upx;
		line-height: 62upx;
		border-radius: 8upx;
	}

	.login-margin-b {
		margin-bottom: 25upx;
	}

	.login-input {
		padding: 10upx 20upx;
	}

	.login-head {
		font-size: 34upx;
		text-align: center;
		padding: 25upx 10upx 55upx 10upx;
	}
.login-head image{ width: 200upx;}
	.login-card {
		background: #fff;
		border-radius: 12upx;
		padding: 10upx 25upx;
		box-shadow: 0 6upx 18upx rgba(0, 0, 0, 0.12);
		position: relative;
		margin-top: 120upx;
	}

	.login-bg {
		height: 260upx;
		padding: 25upx;
		/* background: linear-gradient(#86B5F4, #4790EF); */
		background: #ffbc32;
	}
</style>
