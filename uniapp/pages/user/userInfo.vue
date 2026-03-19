<template>
	<view :style="themeVarsStyle">
		<cu-custom bgColor="bg-gradual-purple" :isBack="true">
			<block slot="backText">返回</block>
			<block slot="content">个人信息</block>
		</cu-custom>
		<view class="complete">
			<view class="info">
				<view class="item flex_layout justify-between solid-bottom">
					<view class="left">上传头像：</view>
					<view class="right flex_layout justify-end">
						<!-- #ifdef MP-WEIXIN -->
						<button class="avatar-wrapper" open-type="chooseAvatar" @chooseavatar="onChooseAvatar">
							<image class="avatar" :src="avatarUrl"></image>
						</button> 
						<!-- #endif -->
						<!-- #ifndef MP-WEIXIN -->
						<uploadImg ref='gUpload' type="avatar" :mode="imgList" :maxCount="maxCount" @chooseFile='chooseFileTest'></uploadImg>
						<!-- #endif -->
					</view>
				</view>
				<view class="item flex_layout">
					<view class="left">名称：</view>
					<view class="right">
						<input type="nickname" v-model="nickname" class="weui-input" placeholder="请输入昵称"/>
					</view>
				</view>
				<view class="item item-bio">
					<view class="left">简介：</view>
					<view class="right">
						<view class="bio-shell">
							<textarea
								v-model="bio"
								class="bio-textarea"
								maxlength="60"
								placeholder="介绍一下你自己吧"
							></textarea>
						</view>
						<view class="bio-count">{{ bioLength }}/60</view>
					</view>
				</view>
				<view class="item flex_layout justify-between">
					<view class="left">性别：</view>
					<view class="right">
						<radio-group class="flex_layout" @change="radioChange">
							<label class="flex_layout radio_view margin-right-xl">
								<view>
									<radio style="transform:scale(0.7)" value="1" :checked="gender == 1" />
								</view>
								<view>男</view>
							</label>
							<label class="flex_layout radio_view">
								<view>
									<radio style="transform:scale(0.7)" value="0" :checked="gender == 0" />
								</view>
								<view>女</view>
							</label>
						</radio-group>
					</view>
				</view>
			</view>
		</view>
		<view class="change padding margin-top-sm" v-if="userInfo" @tap="profile()">
			<!--提交按钮-->
			<view class="sunmit_btn">
				<button class="bg-commom bg-purple">提交</button>
			</view>
		</view>
		
		<!-- 隐私协议 -->
		 <!-- #ifdef MP-WEIXIN -->
		 <!-- 显示在底部 -->
		 <!-- <privacy-popup ref="privacyComponent" position="bottom" ></privacy-popup> -->
		 <!-- 显示在中间 -->
		 <privacy-popup ref="privacyComponent"  ></privacy-popup>
		 <!-- #endif -->
	</view>
</template>

<script>
	import uploadImg from "../../components/uploadImg/uploadImg.vue"
	import PrivacyPopup from '@/components/privacy-popup/privacy-popup.vue';
	import {
		  cdnUrl,
			baseUrl,
			baseApiUrl
		} from '@/config/config.js';
	export default {
		components:{
			uploadImg,
			PrivacyPopup
		},
		data() {
			return {
				user:'',
				avatarUrl:'../../static/user.png',
				avatar:'',
				nickname:'',
				mobile:'',
				bio:'',
				userInfo:[],
				maxCount:1,
				imgList:[],
				gender:0
			}
		},
		computed:{
			bioLength(){
				return String(this.bio || '').length;
			}
		},
		onLoad() {
			
		},
		onShow() {
			// this.userInfo = uni.getStorageSync('user')
			// if (typeof(this.userInfo)== "undefined" || this.userInfo=='' || this.userInfo==[] ||  this.userInfo==null) {
			// 	this.$common.navigateTo('login');
			// }else{
			// 	this.refreshUser()
			// }
			this.refreshUser()
		},
		methods: {
			isGeneratedAvatar(value){
				return /^data:image\//i.test(String(value || '').trim());
			},
			refreshUser(){
				//各个版本的登录判断。微信浏览器授权，微信小程序授权，其他h5跳转登陆
				this.$api.refreshUser(
				 {},
				data => {
					if(data.code==1){
						let user=data.data.user;
						this.refreshAppTheme(user);
						this.imgList = user.avatar ? [user.avatar] : []
						this.avatarUrl=user.avatar
						this.mobile=user.mobile
						this.avatar=this.isGeneratedAvatar(user.avatar) ? '' : user.avatar
						this.nickname=user.nickname
						this.bio=user.bio || ''
						this.gender=user.gender
					}else{
						this.$common.errorToShow(val.msg);
						this.userInfo='';
						this.$db.del('user');
						this.$db.del('auth');
						this.$db.del('token');
						uni.navigateTo({
							url:'/pages/user/login'
						})
					}
				})
			},
			//性别选择
			radioChange(e){
				let value=e.detail.value
				this.gender=value
			},
			onChooseAvatar(e) {
				let userToken = '';
				let auth = this.$db.get("auth");
				userToken = auth.token;
				const { avatarUrl } = e.detail 
				uni.uploadFile({
					url: baseApiUrl + 'common/upload?token='+userToken,
					filePath: avatarUrl,
					fileType: 'image',
					name: 'file',
					headers: {
						'Accept': 'application/json',
						'Content-Type': 'multipart/form-data',
						'token': userToken
					},
					formData: {},
					success: (uploadFileRes) => {
						var dataimg=JSON.parse(uploadFileRes.data);
						this.avatarUrl=dataimg.data.fullurl
						this.avatar=dataimg.data.url
					},
					fail: (error) => {
						if (error && error.response) {
							this.$common.showError(error.response);
						}
					},
					complete: () => {
						setTimeout(function () {
							uni.hideLoading();
						}, 250);
					},
				});
			},
			chooseFileTest(list){
				this.imgList=list
				this.avatar=list.toString()
			},
			profile(){
				this.$api.profile(
				 {
					nickname:String(this.nickname || '').trim(),
					avatar:this.isGeneratedAvatar(this.avatar) ? '' : this.avatar,
					bio:String(this.bio || '').trim(),
					gender:this.gender
				 },
				data => {
					if(data.code==1){
						this.$common.normalToShow(data.msg,function(){
							uni.navigateBack()
						})
					}else{
						this.$common.errorToShow(data.msg)
					}
				}) 
			}
		}
	}
</script>

<style lang="scss">
  page{background: #fafafa;}
	.complete{
		background: #fff;
		.info{
			.item{
				padding: 30rpx;
				.left{color: #333;font-size: 28rpx;width: 180rpx;}
				.top{color: #333;font-size: 28rpx;width: 180rpx;}
				.bottom{
					
				}
				.avatar{
					width: 88rpx;height: 88rpx;display: block;margin: 0;padding: 0;background: none;border: none;
					image{width: 100%;height: 100%;border-radius: 100%;display: block;}
				}
				button::after{display: none;}
				.right{
					flex: 1;
					button{margin: 0;padding: 0;}
				}
				.right input{width: 510rpx;color: #333;font-size: 28rpx;padding-left: 20rpx;text-align: right;}
				.bio-shell{
					width: 100%;
				}
				.bio-textarea{
					width: 100%;
					min-height: 120rpx;
					padding: 20rpx 22rpx;
					box-sizing: border-box;
					border-radius: 18rpx;
					background: #ffffff;
					box-shadow: inset 0 0 0 1rpx #edf2f7;
					font-size: 28rpx;
					line-height: 1.65;
					letter-spacing: 0.5rpx;
					color: #333;
					text-align: left;
				}
				.bio-count{
					margin-top: 10rpx;
					padding-right: 4rpx;
					font-size: 20rpx;
					line-height: 1;
					text-align: right;
					color: #94a3b8;
				}
			}
			.item-bio{
				display: block;
				padding-top: 24rpx;
				.left{
					width: auto;
					margin-bottom: 12rpx;
				}
				.right{
					width: 100%;
				}
			}
		}
	}
	.change{color: #333;font-size: 28rpx;text-align: center;background: #fff;}
	.sunmit_btn{padding: 40rpx;}
	.sunmit_btn button{font-size: 36rpx; height: 74rpx; line-height: 74rpx; width: 360rpx; border-radius: 74rpx; margin: 0 auto; padding: 0; display: block;}
	.sunmit_btn button::after{display: none;}
</style>



