<template>
	<view>
		<view v-if="showPrivacy" :class="privacyClass">
			<view :class="contentClass">
				<view class="title">隐私保护指引</view>
				<view class="des">
					感谢选择我们的小程序，我们非常重视您的个人信息安全和隐私保护。根据最新法律要求，使用我们的产品前，请仔细阅读“<text class="link"
						@tap="openPrivacyContract">{{privacyContractName}}
					</text>”，以便我们向您提供更优质的服务！<br />我们将尽全力保护您的个人信息及合法权益，感谢您的信任！
		
					<br />
				</view>
				<view class="btns">
					<button class="item reject" @tap="exitMiniProgram">拒绝</button>
					<button id="agree-btn" class="item agree" open-type="agreePrivacyAuthorization"
						@agreeprivacyauthorization="handleAgreePrivacyAuthorization">同意</button>
				</view>
			</view>
		</view>
	</view>
	
</template>

<script>
	export default {
		name: 'privacyPopup',
		data() {
			return {
				privacyContractName: '',
				showPrivacy: false,
				isRead: false,
				resolvePrivacyAuthorization: null,
			};
		},
		props: {
			position: {
				type: String,
				default: 'center'
			}
		},
		computed: {
			privacyClass() {
				return this.position === 'bottom' ? 'privacy privacy-bottom' : 'privacy';
			},
			contentClass() {
				return this.position === 'bottom' ? 'content content-bottom' : 'content';
			}
		},
		mounted() {
			if (wx.onNeedPrivacyAuthorization) {
				wx.onNeedPrivacyAuthorization((resolve) => {
					this.resolvePrivacyAuthorization = resolve;
				});
			}
			if (wx.getPrivacySetting) {
				wx.getPrivacySetting({
					success: (res) => {
						console.log('getPrivacySetting',res);
						if (res.needAuthorization) {
							this.privacyContractName = res.privacyContractName;
							this.showPrivacy = true;
						}else{
							this.showPrivacy = false;
						}
					},
				});
			}
		},

		methods: {
			openPrivacyContract() {
				wx.openPrivacyContract({
					success: () => {
						this.isRead = true;
					},
					fail: () => {
						uni.showToast({
							title: '遇到错误',
							icon: 'error',
						});
					},
				});
			},
			exitMiniProgram() {

				wx.exitMiniProgram();

			},
			handleAgreePrivacyAuthorization() {
				// if (this.isRead) {
					this.showPrivacy = false;
					if (typeof this.resolvePrivacyAuthorization === 'function') {
						this.resolvePrivacyAuthorization({
							buttonId: 'agree-btn',
							event: 'agree',
						});
					}
				// } else {
				// 	uni.showToast({
				// 		title: '请先阅读隐私授权协议',
				// 		icon: 'error',
				// 	});
				// }
			},
		},
	};
</script>

<style scoped>
	.privacy {
		position: fixed;
		top: 0;
		right: 0;
		bottom: 0;
		left: 0;
		background: rgba(0, 0, 0, .5);
		z-index: 9999999;
		display: flex;
		align-items: center;
		justify-content: center;
	}

	.privacy-bottom {
		align-items: flex-end;
	}

	.content {
		width: 632rpx;
		height: auto;
		padding: 48rpx;
		box-sizing: border-box;
		background: #fff;
		border-radius: 16rpx;
	}

	.content-bottom {
		position: absolute;
		bottom: 0;
		width: 96%;
		padding: 36rpx;
		padding-bottom: constant(safe-area-inset-bottom);
		padding-bottom: env(safe-area-inset-bottom);
		border-radius: 16rpx 16rpx 0 0;
	}

	.content .title {
		text-align: center;
		color: #333;
		font-weight: bold;
		font-size: 32rpx;
	}

	.content .des {
		font-size: 26rpx;
		color: #666;
		margin-top: 40rpx;
		text-align: justify;
		line-height: 1.6;
	}

	.content .des .link {
		color: #01b276;
		text-decoration: underline;
	}

	.btns {
		margin-top: 48rpx;
		margin-bottom: 12rpx;
		display: flex;
	}

	.btns .item {
		width: 200rpx;
		height: 72rpx;
		overflow: visible;
		display: flex;
		align-items: center;

		justify-content: center;
		/* border-radius: 16rpx; */
		box-sizing: border-box;
		border: none !important;
	}

	.btns .reject {
		background: #f4f4f5;
		color: #666;
		font-size: 14px;
		font-weight: 300;
		margin-right: 16rpx;
	}

	.btns .agree {
		width: 320rpx;
		background: #01b276;
		color: #fff;
		font-size: 16px;

	}

	.privacy-bottom .btns .agree {
		width: 440rpx;

	}
</style>