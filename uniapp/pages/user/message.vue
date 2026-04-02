<template>
	<view :style="themeVarsStyle" class="message-page">
		<cu-custom class="message-cu-topbar" bgColor="bg-white" :isBack="false">
			<block slot="content">
				<view class="message-cu-topbar__title">通知消息</view>
			</block>
		</cu-custom>

		<view class="message-body">
			<view class="message-hero">
				<view class="message-tab-shell">
					<view class="message-tabs">
						<view class="message-tab" :class="0==TabCur?'is-active':''" @tap="tabSelect" data-id="0">
							<text class="cuIcon-all"></text>
							<text>全部</text>
						</view>
						<view class="message-tab" :class="1==TabCur?'is-active':''" @tap="tabSelect" data-id="1">
							<text class="cuIcon-favorfill"></text>
							<text>点赞</text>
						</view>
						<view class="message-tab" :class="2==TabCur?'is-active':''" @tap="tabSelect" data-id="2">
							<text class="cuIcon-attentionfavorfill"></text>
							<text>关注</text>
						</view>
						<view class="message-tab" :class="3==TabCur?'is-active':''" @tap="tabSelect" data-id="3">
							<text class="cuIcon-commentfill"></text>
							<text>评论</text>
						</view>
					</view>
					<button class="cu-btn message-read-btn" @tap="showModal" data-target="gridModal">已读</button>
				</view>
			</view>

			<view class="message-list" v-if="messageLists.length > 0">
				<view
					class="message-card"
					v-for="(item,index) in messageLists"
					:key="index"
					:data-type="item.typedata"
					:data-discover_id="item.discover_id"
					:data-id="item.id"
					:data-index="index"
					:data-user_id="item.user_id"
					@tap="messageClick"
				>
					<view class="message-avatar" v-if="item.avatar!=''" :style="{'background-image':'url('+item.avatar+')'}"></view>
					<view class="message-avatar" v-else style="background-image:url(../../static/images/avatar.png);"></view>

					<view class="message-main">
						<view class="message-main-top">
							<view class="message-name text-cut">{{item.nickname}}</view>
						</view>
						<view class="message-content-row">
							<view class="message-unread-dot" v-if="item.readdata==0"></view>
							<view class="message-content text-cut">{{normalizeMessageContent(item.content)}}</view>
						</view>
						<view class="message-time">{{formatMessageTime(item.createtime)}}</view>
					</view>

					<view class="message-side" v-if="item.typedata!=4">
						<image
							v-if="shouldShowMessageCover(item, index)"
							class="message-cover-image"
							:src="normalizeMessageCoverUrl(item.coverimage)"
							mode="aspectFill"
							@error.stop="onMessageCoverError(item, index)"
						></image>
						<view class="message-cover-fallback" v-else>
							<text>{{messageFallbackText(item)}}</text>
						</view>
					</view>
					<view class="message-side message-side-follow" v-else-if="item.typedata==4">
						<text class="cuIcon-right"></text>
					</view>
				</view>
			</view>

			<view class="message-empty" v-else>
				<view class="message-empty-icon"><text class="cuIcon-messagefill"></text></view>
				<view class="message-empty-title">暂时还没有通知</view>
				<view class="message-empty-text">有人点赞、评论或关注你时，会显示在这里</view>
			</view>

			<view class="text-center text-gray padding-tb-sm" :class="showNoResult?'show':'hide'" v-if="messageLists.length>0">我是有底线的...</view>
		</view>
		<colorbar footerTab="3" :messageCount="messageCount"></colorbar>
	</view>
</template>

<script>
	var page=1;var _this;
	import { cndUrl } from '../../config/config.js';
	import colorbar from "@/components/color-bar.vue"
	export default {
		components: {colorbar },
		data() {
			return {
				TabCur: 0,
				scrollLeft: 0,
				type:0,
				messageLists:[],
				totalPage:0,
				showNoResult:false,
				ids:'',
				messageCount:0,
				user:[],
				statusBarHeight:0,
				topbarHeight:72,
				topbarBottomGap:10,
				cndUrl:cndUrl,
				messageCoverFailedMap:{}
			};
		},
		computed:{
			messageTopbarStyle(){
				return {
					paddingTop: this.statusBarHeight + 'px',
					height: (this.statusBarHeight + this.topbarHeight) + 'px'
				};
			},
			messageBodyStyle(){
				return {
					paddingTop: (this.statusBarHeight + this.topbarHeight + this.topbarBottomGap + 6) + 'px'
				};
			}
		},
		mounted() {
			_this=this;
		},
		onLoad() {
			page=1;
			this.initTopbarMetrics();
		},
		onReachBottom() {
			if(this.totalPage>=page){
				this.showMessageListsDataOp();
			}else{
				this.showNoResult=true
			}
		},
		onShow() {
			this.user = this.$common.userInfo();
			if (typeof(this.user)== "undefined" || this.user=='' ||  this.user==null) {
				this.$common.navigateTo('login');
				return;
			}
			page=1;
			this.ids='';
			this.messageLists=[];
			this.totalPage=0;
			this.showNoResult=false;
			this.$api.refreshUser(
				{},
				val => {
					this.user = val.data.user;
					this.refreshAppTheme(this.user);
					this.auth=val.data.auth;
					this.messageCount=val.data.msgCount;
				})
			this.showMessageListsDataOp();
		},
		methods: {
			initTopbarMetrics(){
				let statusBarHeight=Number(this.StatusBar || 0);
				let systemInfo={};
				try{
					if(typeof wx !== 'undefined' && typeof wx.getWindowInfo === 'function'){
						systemInfo=wx.getWindowInfo() || {};
					}else{
						systemInfo=uni.getSystemInfoSync() || {};
					}
				}catch(error){
					systemInfo={};
				}
				if(!statusBarHeight){
					statusBarHeight=Number(systemInfo.statusBarHeight || 0);
				}
				this.statusBarHeight=statusBarHeight;
			},
			showMessageListsDataOp(){
				this.$api.showMessageListsData(
				{page:page,limit:10,type:this.type},
				data => {
					var res=data.data;
					if (data.code == 1) {
						if(res.list.length<10){
							_this.showNoResult=true;
						}
				page++;
						this.messageLists=this.messageLists.concat(res.list);
						this.totalPage=res.count
						var str='';
						for(var i=0;i<this.messageLists.length;i++){
							 str += this.messageLists[i].id + ",";
						}
						if (str.length > 0) {
							str = str.substr(0,str.length - 1);
						}
						this.ids=str;
					}else{
						this.$common.errorToShow(data.msg);
					}
				}
				)
			},
			messageClick(e){
				var discover_id=e.currentTarget.dataset.discover_id;
				var id=e.currentTarget.dataset.id;
				var typedata=e.currentTarget.dataset.type;
				var user_id=e.currentTarget.dataset.user_id;
				var index=e.currentTarget.dataset.index;
				this.doMessageReadDataOp(id,index);
				if(typedata!=4){
					this.$common.navigateTo('../index/detail?id='+discover_id);
				}
				if(typedata==4){
					this.$common.navigateTo('../index/user?id='+user_id);
				}
			},
		    doMessageReadDataOp(id,index){
				if(_this.ids!=''){
					if(id){
					 var pid=id;
					}else{
					 var pid=_this.ids
					}
					_this.$api.doMessageReadData(
					{ids:pid},
					data => {
						if (data.code == 1) {
							if(!id){
								for(var i=0;i<this.messageLists.length;i++){
									 this.messageLists[i].readdata='1';
								}
								_this.$common.successToShow(data.msg);
							}else{
								this.messageLists[index].readdata='1';
							}
							_this.messageCount=data.data;
						}else{
							if(!id){
								_this.$common.errorToShow(data.msg);
							}
						}
					}
					)
				}else{
					if(!id){
						_this.$common.errorToShow('暂无未读消息');
					}
				}
		    },
			showModal(){
				_this.$common.modelShow('设置提示','确认设置已加载的消息已读吗?',()=>{
					_this.doMessageReadDataOp();
				},function(){},true,'取消','确定')
			},
			tabSelect(e) {
				this.TabCur = e.currentTarget.dataset.id;
				this.scrollLeft = (e.currentTarget.dataset.id - 1) * 60
				this.type=e.currentTarget.dataset.id;
				page=1;
				this.messageLists=[];
				this.totalPage=0;
				this.showNoResult=false
				this.ids=''
				this.showMessageListsDataOp();
			},
			formatMessageTime(value){
				return String(value || '').replace(/\s+/g, ' ').trim();
			},
			normalizeMessageContent(content){
				return String(content || '')
					.replace(/\s+/g, ' ')
					.replace(/作品/g, '动态')
					.trim();
			},
			normalizeMessageCoverUrl(url){
				const value = String(url || '').trim().replace(/^['"]|['"]$/g, '');
				if(!value || value === '[]' || value === 'null' || value === 'undefined'){
					return '';
				}
				if(/^https?:\/\/\/+/i.test(value)){
					const cleanedPath = value.replace(/^https?:\/\/\/+/i, '/');
					return this.buildMessageAssetUrl(cleanedPath);
				}
				if(/^https?:\/\//i.test(value) || /^data:image\//i.test(value)){
					return value;
				}
				return this.buildMessageAssetUrl(value);
			},
			buildMessageAssetUrl(url){
				const value = String(url || '').trim();
				if(!value){
					return '';
				}
				if(/^https?:\/\//i.test(value) || /^data:image\//i.test(value)){
					return value;
				}
				const base = String(this.cndUrl || '').replace(/\/+$/, '');
				const path = value.charAt(0) === '/' ? value : '/' + value;
				return base + path;
			},
			hasMessageCover(url){
				const finalUrl = this.normalizeMessageCoverUrl(url);
				if(!finalUrl){
					return false;
				}
				const cleanUrl = finalUrl.split('?')[0].toLowerCase();
				return /\.(jpg|jpeg|png|gif|bmp|webp|svg)$/i.test(cleanUrl);
			},
			getMessageCoverKey(item, index){
				const itemId = item && item.id ? item.id : index;
				return 'msg-cover-' + itemId + '-' + index;
			},
			shouldShowMessageCover(item, index){
				const finalUrl = this.normalizeMessageCoverUrl(item && item.coverimage ? item.coverimage : '');
				if(!this.hasMessageCover(finalUrl)){
					return false;
				}
				return !this.messageCoverFailedMap[this.getMessageCoverKey(item, index)];
			},
			onMessageCoverError(item, index){
				this.$set(this.messageCoverFailedMap, this.getMessageCoverKey(item, index), true);
			},
			messageFallbackText(item){
				const source = String((item && (item.title || item.content)) || '').replace(/\s+/g, ' ').trim();
				if(!source){
					return '帖子';
				}
				return source.slice(0, 4);
			}
		}
	}
</script>

<style>
	.message-page{
		min-height: 100vh;
		background: #f4f6fb;
	}
	.message-cu-topbar .cu-bar{
		box-shadow: none;
		border-bottom: 1rpx solid rgba(226, 232, 240, 0.72);
	}
	.message-cu-topbar .cu-bar .content{
		left: 24rpx;
		right: 220upx;
		width: auto;
		text-align: left;
	}
	.message-cu-topbar__title{
		font-size: 30rpx;
		line-height: 1;
		color:#111827;
		font-weight: 400;
		letter-spacing: .6rpx;
		text-align: left;
		transform: translateY(12rpx);
		padding-left: 15rpx;
	}
	.message-body{
		padding: 8rpx 0 180rpx;
	}
	.message-hero{
		padding: 12rpx 24rpx 20rpx;
	}
	.message-tab-shell{
		display: flex;
		align-items: center;
		gap: 8rpx;
		background: #ffffff;
		border-radius: 28rpx;
		padding: 10rpx 12rpx;
		box-shadow: 0 12rpx 36rpx rgba(38, 63, 128, 0.08);
	}
	.message-tabs{
		flex: 1;
		display: flex;
		align-items: center;
		gap: 8rpx;
		min-width: 0;
	}
	.message-tab{
		display: inline-flex;
		align-items: center;
		justify-content: center;
		flex: 1 1 0;
		min-width: 0;
		height: 52rpx;
		padding: 0 10rpx;
		border-radius: 999rpx;
		background: #ffffff;
		color: #6f7c91;
		font-size: 22rpx;
		font-weight: 500;
		white-space: nowrap;
	}
	.message-tab text:first-child{
		margin-right: 4rpx;
		font-size: 20rpx;
		flex-shrink: 0;
	}
	.message-tab text:last-child{
		min-width: 0;
		overflow: hidden;
		text-overflow: ellipsis;
		white-space: nowrap;
	}
	.message-tab.is-active{
		background: linear-gradient(180deg, #8ebcf6 0%, #7daef0 100%);
		border: none;
		color: #6b8fd9;
		color: #ffffff;
		box-shadow: 0 8rpx 18rpx rgba(77, 113, 187, 0.18);
	}
	.message-read-btn{
		display: inline-flex;
		align-items: center;
		justify-content: center;
		flex-shrink: 0;
		height: 42rpx;
		min-width: 52rpx;
		padding: 0 14rpx !important;
		border-radius: 999rpx;
		background: #eef3ff !important;
		color: #5f7ee8 !important;
		font-size: 20rpx;
		font-weight: 500;
		border: none;
		box-shadow: none;
	}
	.message-read-btn::after{
		border: none;
	}
	.message-list{
		padding: 0 24rpx;
	}
	.message-card{
		display: flex;
		align-items: center;
		padding: 28rpx;
		margin-bottom: 24rpx;
		background: #ffffff;
		border-radius: 28rpx;
		box-shadow: 0 12rpx 36rpx rgba(38, 63, 128, 0.08);
	}
	.message-avatar{
		width: 72rpx;
		height: 72rpx;
		border-radius: 50%;
		background-color: #e9eef5;
		background-position: center;
		background-size: cover;
		background-repeat: no-repeat;
		flex-shrink: 0;
	}
	.message-main{
		flex: 1;
		min-width: 0;
		padding: 0 12rpx 0 20rpx;
	}
	.message-main-top{
		display: flex;
		align-items: center;
		justify-content: flex-start;
	}
	.message-name{
		flex: 1;
		min-width: 0;
		font-size: 32rpx;
		font-weight: 600;
		line-height: 1.2;
		color: #1f2a44;
	}
	.message-content-row{
		display: flex;
		align-items: center;
		margin-top: 10rpx;
	}
	.message-unread-dot{
		width: 12rpx;
		height: 12rpx;
		margin-right: 12rpx;
		border-radius: 50%;
		background: #ef4444;
		flex-shrink: 0;
	}
	.message-content{
		flex: 1;
		min-width: 0;
		font-size: 24rpx;
		line-height: 1.5;
		color: #7b8798;
	}
	.message-time{
		margin-top: 10rpx;
		font-size: 24rpx;
		line-height: 1.2;
		color: #99a0b0;
	}
	.message-side{
		width: 84rpx;
		display: flex;
		align-items: center;
		justify-content: flex-end;
		flex-shrink: 0;
	}
	.message-cover{
		width: 72rpx;
		height: 72rpx;
		border-radius: 20rpx;
		background: #e5e7eb;
		background-position: center;
		background-size: cover;
		background-repeat: no-repeat;
	}
	.message-cover-image{
		width: 72rpx;
		height: 72rpx;
		border-radius: 20rpx;
		background: #e5e7eb;
		display: block;
	}
	.message-cover-fallback{
		width: 72rpx;
		height: 72rpx;
		border-radius: 20rpx;
		background: linear-gradient(180deg, #eef3fb 0%, #e5ebf6 100%);
		display: flex;
		align-items: center;
		justify-content: center;
		padding: 8rpx;
		box-sizing: border-box;
		text-align: center;
	}
	.message-cover-fallback text{
		font-size: 18rpx;
		line-height: 1.35;
		color: #6b7280;
		word-break: break-all;
		display: -webkit-box;
		-webkit-box-orient: vertical;
		-webkit-line-clamp: 2;
		overflow: hidden;
	}
	.message-side-follow{
		color: #c5cfdb;
		font-size: 28rpx;
	}
	.message-empty{
		padding: 120rpx 48rpx 0;
		text-align: center;
	}
	.message-empty-icon{
		width: 112rpx;
		height: 112rpx;
		margin: 0 auto;
		border-radius: 36rpx;
		background: rgba(143,191,246,0.16);
		display: flex;
		align-items: center;
		justify-content: center;
	}
	.message-empty-icon text{
		font-size: 52rpx;
		color: #8fbff6;
	}
	.message-empty-title{
		margin-top: 24rpx;
		font-size: 30rpx;
		font-weight: 600;
		color: #334155;
	}
	.message-empty-text{
		margin-top: 10rpx;
		font-size: 22rpx;
		line-height: 1.6;
		color: #94a3b8;
	}
	.show{ display: block;}
	.hide{ display: none;}
</style>
