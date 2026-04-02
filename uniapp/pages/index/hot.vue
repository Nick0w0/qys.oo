<template>
	<view class="follow-feed-page" :style="themeVarsStyle">
		<cu-custom class="follow-cu-topbar" bgColor="bg-white" :isBack="false">
			<block slot="content">
				<view class="follow-cu-topbar__title">{{ followPageTitle }}</view>
			</block>
		</cu-custom>

		<view class="follow-feed-body">
			<block v-if="attentionDataLists.length > 0">
				<view class="follow-card" v-for="(item,index) in attentionDataLists" :key="index">
					<view class="follow-card__header">
						<view class="follow-card__avatar cu-avatar round lg" v-if="item.avatar!=''" :style="{'background-image':'url('+item.avatar+')'}" :data-id="item.id" @tap="detail"></view>
						<view class="follow-card__avatar cu-avatar round lg" v-else style="background-image: url(../../static/images/avatar.png);" :data-id="item.id" @tap="detail"></view>
						<view class="follow-card__meta flex-sub" :data-id="item.id" @tap="detail">
							<view class="follow-card__name-row">
								<view class="follow-card__name text-cut">{{item.nickname}}</view>
								<view class="follow-card__time">{{item.createtime}}</view>
							</view>
						</view>
					</view>

					<view class="follow-card__content" :data-id="item.id" @tap="detail">
						{{item.content}}
					</view>

					<view
						v-if="item.coverimages && item.coverimages.length"
						class="follow-card__gallery"
						:class="item.coverLayoutClass"
					>
						<view
							class="follow-card__image"
							v-for="(item2,index2) in item.coverimages"
							:key="index2"
							:data-index="index"
							:data-index2="index2"
							:style="{'background-image':'url('+item2+')'}"
							@tap.stop="previewImage"
						></view>
					</view>

					<view class="follow-card__stats">
						<view class="follow-card__stat">
							<text class="cuIcon-attentionfill"></text>
							<text>{{item.browse}}</text>
						</view>
						<view class="follow-card__stat" data-type="1" :data-id="item.id" :data-index="index" data-index2="0" @tap.stop="dolikeDataOp">
							<text class="cuIcon-appreciatefill" :class="item.isfavor==1?'text-red':''"></text>
							<text>{{item.favorNum}}</text>
						</view>
						<view class="follow-card__stat" :data-id="item.id" @tap="detail">
							<text class="cuIcon-messagefill"></text>
							<text>{{item.commentNum}}</text>
						</view>
					</view>

				</view>
			</block>

			<view class="follow-empty" v-else-if="showNoResult">
				<view class="follow-empty__icon cuIcon-attentionfavor"></view>
				<view class="follow-empty__title">还没有关注动态</view>
				<view class="follow-empty__desc">去关注一些同学，新的动态会显示在这里</view>
			</view>

			<view class="follow-feed-end text-center text-gray padding-tb-sm" v-if="attentionDataLists.length > 0 && showNoResult">已经到底了</view>
		</view>
		 <colorbar footerTab="1" :messageCount="messageCount"></colorbar>
	</view>
</template>

<script>
	var _this;var page;
	import {cndUrl} from '../../config/config.js';
	import colorbar from "@/components/color-bar.vue"
	export default {
		components: {colorbar },
		data() {
			return {
				cndUrl:cndUrl,
				isCard: false,
				isThumb:false,
				iscomment:false,
				isfollow:false,
				thumbNum:20,
				imgList:['https://ossweb-img.qq.com/images/lol/web201310/skin/big10006.jpg','http://dissona.usiyi.com/assets/dissona/images/unique2.jpg','https://ossweb-img.qq.com/images/lol/web201310/skin/big10006.jpg','https://ossweb-img.qq.com/images/lol/web201310/skin/big10006.jpg','https://ossweb-img.qq.com/images/lol/web201310/skin/big10006.jpg','https://ossweb-img.qq.com/images/lol/web201310/skin/big10006.jpg','https://ossweb-img.qq.com/images/lol/web201310/skin/big10006.jpg','https://ossweb-img.qq.com/images/lol/web201310/skin/big10006.jpg','https://ossweb-img.qq.com/images/lol/web201310/skin/big10006.jpg'],
				attentionDataLists:[],
				loading:true,
				showNoResult:false,
				user:[],
				messageCount:0,
				statusBarHeight:0,
				topbarHeight:72,
				topbarBottomGap:10
				
			};
		},
		computed:{
			schoolInfo(){
				return this.user && this.user.school_info ? this.user.school_info : {};
			},
			followPageTitle(){
				return '关注动态';
			},
			followTopbarStyle(){
				return {
					paddingTop: this.statusBarHeight + 'px',
					height: (this.statusBarHeight + this.topbarHeight) + 'px'
				};
			},
			followBodyStyle(){
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
		onShow() {
			this.user = this.$common.userInfo();
			console.log("this.user: ",this.user);
			if (typeof(this.user)== "undefined" || this.user=='' ||  this.user==null) {
				this.$common.navigateTo('login');
				return;
			}
			page=1;
			this.attentionDataLists=[];
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
			this.attentionDataOp();
		},
		onReachBottom() {
			if(this.totalPage>=page){
				this.attentionDataOp();
			}else{
				this.showNoResult=true
			}
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
			//处理表情编号（处理成带img标签）
			handleImg(content){
				var a=content.match(/\[em_([0-97]*)]/g);
				if(a){
					a.map(item=>{
						var text=item.replace(/\[em_/g,'<img src="'+this.cndUrl+'/uploads/uni-emoji/')
						var text1=text.replace(/\]/g,'.gif" align="middle">')
						content=content.replace(item,text1);
					})
					return content;
				}else{
					return content;
				}
				
			},
			attentionDataOp(){
					this.$api.attentionData(
					{page:page,limit:10},
					data => {
						console.log(data);
						if (data.code == 1) {
							var res=data.data || {};
							var list=Array.isArray(res.list)?res.list:[];
							if(list.length<10){
								_this.showNoResult=true;
							}
							page++;
							_this.attentionDataLists=_this.attentionDataLists.concat(list);
							_this.attentionDataLists=_this.attentionDataLists.map(item=>{
								item.coverimages=Array.isArray(item.coverimages)?item.coverimages.filter(Boolean):[];
								item.comment_lists=Array.isArray(item.comment_lists)?item.comment_lists:[];
								item.comment_lists=item.comment_lists.map(items=>{
									items.replyLists=Array.isArray(items.replyLists)?items.replyLists:[];
									return {
										...items,
										content:this.handleImg(items.content)
									}
								})
								return{
									...item,
									coverLayoutClass:this.getCoverLayoutClass(item.coverimages)
								}
							})
							console.log(_this.attentionDataLists);
							_this.totalPage=res.total_page;
							
						}else{
							if(page===1){
								_this.showNoResult=true;
							}
							if(data.msg!='暂无数据'){
								_this.$common.errorToShow(data.msg);
							}
						}
					}
					)  
			},
			getCoverLayoutClass(images){
				var length=Array.isArray(images)?images.length:0;
				if(length<=1){
					return 'is-single';
				}
				if(length===2){
					return 'is-double';
				}
				return 'is-grid';
			},
			//作品/评论点赞
			   /**
			     * 点赞
			     *
			     * @param string $discover_id 发现id
			     * @param string $comment_id 评论id
			     * @param string $type 点赞类型 1 作品点赞，  2，评论点赞
			     */ 
			dolikeDataOp(e){
				   
				    var type= e.currentTarget.dataset.type;
					var comment_id=0;
					//动态的index
					var index=e.currentTarget.dataset.index;
					//第一评论的index
					var index2=e.currentTarget.dataset.index2;
					var id=e.currentTarget.dataset.id;
					if(type==2){
						comment_id= e.currentTarget.dataset.commentid;
					}
					console.log("comment_id: ",comment_id);
					if(_this.loading){
						_this.loading=false;
						_this.$api.dolikeData(
						{type:type,discover_id:id,comment_id:comment_id},
						data => {
							console.log(data);
							var res=data.data;
							 _this.loading=true;
							if (data.code == 1) {
								_this.$common.normalToShow(data.msg);
								if(type==1){
									console.log("_this.attentionDataLists[index].favorNum: ",index);
									if(_this.attentionDataLists[index].isfavor){
										if(_this.attentionDataLists[index].favorNum>0){
											console.log("_this.attentionDataLists[index].favorNum: ",_this.attentionDataLists[index].favorNum);
											_this.attentionDataLists[index].favorNum--
										}
									}else{
										_this.attentionDataLists[index].favorNum++
									}
									_this.attentionDataLists[index].isfavor=!_this.attentionDataLists[index].isfavor;
									
								}else{
									if(_this.attentionDataLists[index]['comment_lists'][index2].isfavor){
										if(_this.attentionDataLists[index]['comment_lists'][index2].favorCount>0){
											_this.attentionDataLists[index]['comment_lists'][index2].favorCount--
										}
									}else{
										_this.attentionDataLists[index]['comment_lists'][index2].favorCount++
									}
									_this.attentionDataLists[index]['comment_lists'][index2].isfavor=!_this.attentionDataLists[index]['comment_lists'][index2].isfavor;
									
									
								}
								
								
							}else{
								_this.$common.normalToShow(data.msg);
							}
						}
						)  
					}
					
			},
			//作品/发表评论回复
			doCommentDataOp(e){
					this.$api.doCommentData(
					//{type:1,discover_id:19,content:'我在给作品点赞'},
					{type:2,comment_id:3,content:'我回复了id3的评论',discover_id:19},
					data => {
						console.log(data);
						var res=data.data;
						if (data.code == 1) {
							
						}
					}
					)  
			},
			//关注
			doAttentionDataOp(e){
				   var attention_id= e.currentTarget.dataset.userid;
				   var id= e.currentTarget.dataset.id;
					this.$api.doAttentionData(
					{attention_id:attention_id,discover_id:id},
					data => {
						console.log(data);
						if (data.code == 1) {
							page=1;
							_this.attentionDataLists=[];
							_this.totalPage=0;
							_this.attentionDataOp();
						  _this.$common.successToShow(data.msg);	
						}else{
							_this.$common.errorToShow(data.msg);	
						}
					}
					)  
			},
			
			IsCard(e) {
				this.isCard = e.detail.value
			},
			detail(e){
				var id=e.currentTarget.dataset.id;
				this.$common.navigateTo('detail?id='+id)
			},
			thumbUp(status){
				if(status){
					this.isThumb=false;
					this.thumbNum--
				}else{
					this.isThumb=true;
					this.thumbNum++
				}
			},
			commentTab(status){
				if(status){
					this.iscomment=false;
				}else{
					this.iscomment=true;
				}
			},
			showModal(status){
				if(status){
					this.isfollow=false;
				}else{
					this.isfollow=true;
				}
			},
			previewImage(e) {
				//动态的index
				var index=e.currentTarget.dataset.index;
				//第一评论的index
				var index2=e.currentTarget.dataset.index2;
				console.log("index: ",index);
				let imgUrls = this.attentionDataLists[index].coverimages;
				uni.previewImage({
				  current: imgUrls[index2],
				  urls: imgUrls,
							complete: res => {
								//console.log(res);
							}
				})
			 },
			openSearch(){
				this.$common.navigateTo('/pages/index/search');
			}
		}
	}
</script>

<style>
.follow-feed-page{
	min-height: 100vh;
	background: #f4f6fb;
}
.follow-cu-topbar .cu-bar{
	box-shadow: none;
	border-bottom: 1rpx solid rgba(226, 232, 240, 0.72);
}
.follow-cu-topbar .cu-bar .content{
	left: 24rpx;
	right: 220upx;
	width: auto;
	text-align: left;
}
.follow-cu-topbar__title{
	font-size: 30rpx;
	line-height: 1;
	color:#111827;
	font-weight: 400;
	letter-spacing: .6rpx;
	text-align: left;
	transform: translateY(12rpx);
	padding-left: 15rpx;
}

.follow-feed-body{
	padding: 12rpx 24rpx 180rpx;
}

.follow-card{
	margin-bottom: 24rpx;
	padding: 28rpx;
	background: #ffffff;
	border-radius: 28rpx;
	box-shadow: 0 12rpx 36rpx rgba(38, 63, 128, 0.08);
}

.follow-card__header{
	display: flex;
	align-items: center;
}

.follow-card__avatar{
	flex-shrink: 0;
	width: 68rpx;
	height: 68rpx;
	background-size: cover;
	background-position: center;
}

.follow-card__meta{
	min-width: 0;
	margin-left: 16rpx;
}

.follow-card__name-row{
	display: flex;
	flex-direction: column;
	gap: 10rpx;
}

.follow-card__name{
	font-size: 26rpx;
	font-weight: 500;
	color: #2f3d56;
}

.follow-card__time{
	font-size: 20rpx;
	color: #99a0b0;
}

.follow-card__content{
	margin-top: 24rpx;
	font-size: 26rpx;
	line-height: 1.8;
	color: #334155;
	word-break: break-all;
}

.follow-card__gallery{
	display: grid;
	gap: 14rpx;
	margin-top: 22rpx;
	justify-content: flex-start;
}

.follow-card__gallery.is-single{
	grid-template-columns: 360rpx;
}

.follow-card__gallery.is-double{
	grid-template-columns: repeat(2, 220rpx);
}

.follow-card__gallery.is-grid{
	grid-template-columns: repeat(3, 180rpx);
}

.follow-card__image{
	width: 100%;
	padding-top: 100%;
	border-radius: 20rpx;
	background-color: #edf1fb;
	background-position: center;
	background-size: cover;
	overflow: hidden;
}

.follow-card__gallery.is-single .follow-card__image{
	padding-top: 78%;
}

.follow-card__stats{
	display: flex;
	align-items: center;
	gap: 28rpx;
	margin-top: 24rpx;
	padding-top: 22rpx;
	border-top: 1rpx solid #eef1f7;
	color: #8a93a6;
	font-size: 22rpx;
}

.follow-card__stat{
	display: inline-flex;
	align-items: center;
	gap: 8rpx;
}

.follow-empty{
	padding: 120rpx 40rpx 0;
	text-align: center;
	color: #8f98aa;
}

.follow-empty__icon{
	font-size: 96rpx;
	color: #9db1f3;
}

.follow-empty__title{
	margin-top: 24rpx;
	font-size: 34rpx;
	font-weight: 600;
	color: #33415c;
}

.follow-empty__desc{
	margin-top: 14rpx;
	font-size: 26rpx;
	line-height: 1.6;
}

.follow-feed-end{
	color: #9ea6b5;
}
</style>



