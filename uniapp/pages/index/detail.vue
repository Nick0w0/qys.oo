<template>
	<view :style="themeVarsStyle" class="detail-page">
		<cu-custom bgColor="bg-gradual-purple" :isBack="true">
			<block slot="backText">返回</block>
			<block slot="content">详情</block>
		</cu-custom>

		<view class="detail-page-body" v-if="loadFinish">
			<view class="detail-post-card">
				<view class="detail-post-head">
					<view class="detail-post-user">
						<image class="detail-post-avatar" v-if="detailDatas.avatar" :src="detailDatas.avatar" mode="aspectFill"></image>
						<image class="detail-post-avatar" v-else src="../../static/images/avatar.png" mode="aspectFill"></image>
						<view class="detail-post-user-main">
							<view class="detail-post-name">{{detailDatas.nickname}}</view>
							<view class="detail-post-time">{{detailDatas.createtime}}</view>
						</view>
					</view>
					<button class="cu-btn detail-follow-btn" :class="isfollow ? 'is-active' : ''" @tap="doAttentionDataOp">
						<text class="cuIcon-attentionfavorfill"></text>
						<text>{{ isfollow ? '已关注' : '关注' }}</text>
					</button>
				</view>

				<view class="detail-post-title" v-if="detailDatas.title && detailDatas.content && detailDatas.title !== detailDatas.content">{{detailDatas.title}}</view>
				<view class="detail-post-text" v-if="detailDatas.content">{{detailDatas.content}}</view>
				<view class="detail-post-text" v-else-if="detailDatas.title">{{detailDatas.title}}</view>

				<view class="detail-post-video" v-if="videoSrc">
					<video :src="videoSrc" :show-play-btn="true" :controls="true" objectFit="cover"></video>
				</view>

				<view class="detail-post-media-grid" v-if="swiperList.length>0">
					<view class="detail-post-media-item" :class="swiperList.length === 1 ? 'is-single' : ''" v-for="(item,index) in swiperList" :key="index" @tap="lookImg(index,item.type)">
						<image :src="item.url" mode="aspectFill"></image>
					</view>
				</view>

				<view class="detail-post-stats">
					<view class="detail-post-tag" v-if="detailTagText">
						<text class="cuIcon-tagfill"></text>
						<text>{{detailTagText}}</text>
					</view>
					<view class="detail-post-stats-right">
						<view class="detail-post-stat">
							<text class="cuIcon-attentionfill"></text>
							<text>{{detailDatas.browse || 0}}</text>
						</view>
						<view class="detail-post-stat" data-type="1" @tap="dolikeDataOp">
							<text class="cuIcon-appreciatefill" :class="isThumb ? 'is-highlight' : ''"></text>
							<text>{{detailDatas.favorNum || 0}}</text>
						</view>
						<view class="detail-post-stat">
							<text class="cuIcon-messagefill"></text>
							<text>{{detailDatas.commentNum || 0}}</text>
						</view>
					</view>
				</view>
			</view>

			<view class="detail-comment-section">
				<view class="detail-section-head">
					<view class="detail-section-title">评论 {{detailDatas.commentNum || 0}}</view>
					<view class="detail-section-note">最新评论</view>
				</view>

				<view v-if="commentLists.length>0">
					<view class="detail-comment-card" v-for="(item,index) in commentLists" :key="index">
						<image class="detail-comment-avatar" v-if="item.avatar!=''" :src="item.avatar" mode="aspectFill" @tap="replyBtn(item.id,item.id,item.nickname)"></image>
						<image class="detail-comment-avatar" v-else src="../../static/images/avatar.png" mode="aspectFill" @tap="replyBtn(item.id,item.id,item.nickname)"></image>
						<view class="detail-comment-main">
							<view class="detail-comment-name" @tap="replyBtn(item.id,item.id,item.nickname)">{{item.nickname}}</view>
							<view class="detail-comment-text" @tap="replyBtn(item.id,item.id,item.nickname)">
								<rich-text :nodes="item.content"></rich-text>
							</view>

							<view class="detail-comment-replies" v-if="item.replyLists.length>0">
								<view class="detail-comment-reply" v-for="(item2,index2) in item.replyLists" :key="index2" @tap="replyBtn(item2.id,item.id,item2.rnickname)">
									<view class="detail-comment-reply-head">
										<view class="detail-comment-reply-user">
											<text class="reply-user-name">{{item2.rnickname}}</text>
											<text class="reply-user-join"> 回复 </text>
											<text class="reply-user-target">{{item2.nickname}}</text>
										</view>
										<text class="detail-comment-reply-time">{{item2.createtime}}</text>
									</view>
									<rich-text class="detail-comment-reply-text" :nodes="item2.content"></rich-text>
								</view>
							</view>

							<view class="detail-comment-foot">
								<view class="detail-comment-time">{{item.createtime}}</view>
								<view class="detail-comment-actions">
									<view class="detail-comment-action" data-type="2" :data-commentid="item.id" :data-index="index" @tap="dolikeDataOp">
										<text class="cuIcon-appreciatefill" :class="item.isfavor==1 ? 'is-highlight' : ''"></text>
										<text>{{item.favorCount}}</text>
									</view>
									<view class="detail-comment-action" @tap="replyBtn(item.id,item.id,item.nickname)">
										<text class="cuIcon-messagefill"></text>
										<text>{{item.commentCount}}</text>
									</view>
								</view>
							</view>
						</view>
					</view>
				</view>

				<view class="detail-empty-state" v-else>
					<view class="detail-empty-icon">
						<text class="cuIcon-messagefill"></text>
					</view>
					<view class="detail-empty-title">暂无评论</view>
					<view class="detail-empty-text">抢先一步抢沙发吧</view>
				</view>
			</view>
		</view>

		<view class="detail-loading" v-else>加载中...</view>

		<view class="detail-bottom-space" v-if="detailDatas.iscommentdata=='1'"></view>
		<view class="detail-comment-bar" v-if="detailDatas.iscommentdata=='1'">
			<view class="detail-reply-tip" v-if="focusComment">
				<view class="detail-reply-tip-text">
					<text class="detail-reply-tip-label">回复</text>
					<text class="detail-reply-tip-name">{{replyUserName}}</text>
				</view>
				<text class="cuIcon-close" @tap="cleanReplyer"></text>
			</view>

			<view class="detail-comment-bar-main">
				<view class="detail-comment-input">
					<input
						type="text"
						@input="changeInput"
						@focus="InputFocus"
						@blur="InputBlur"
						:value="inputValue"
						:focus="focusComment"
						placeholder-style="font-size:24rpx;color:#94a3b8;"
						placeholder="写下你的评论..."
						maxlength="300"
					></input>
				</view>
				<view class="detail-comment-emoji" @tap="showEmj">
					<text :class="emojiIcon"></text>
				</view>
				<button class="cu-btn detail-comment-submit" @tap="transmission('')">发送</button>
			</view>
			<emotion @emotion="handleEmj" v-if="isShowEmj"></emotion>
		</view>
	</view>
</template>

<script>
	//var this;
	import {cndUrl} from '../../config/config.js';
	import emotion from '@/components/bkhumor-emoji/bkhumor-emoji.vue';
	export default {
		components:{
			emotion
		},
		computed: {
			detailTagText() {
				const tagIds = String(this.detailDatas.tag_ids || '')
					.split(',')
					.map(item => item.trim())
					.filter(Boolean);
				if (!tagIds.length || !Array.isArray(this.tagOptions) || this.tagOptions.length === 0) {
					return '';
				}
				const labelMap = {};
				this.tagOptions.forEach(item => {
					labelMap[String(item.id)] = item.label || item.name || '';
				});
				return tagIds
					.map(id => labelMap[id])
					.filter(Boolean)
					.join(' / ');
			}
		},
		data() {
			return {
				cndUrl:cndUrl,
				isCard: false,
				cardCur: 0,
				swiperList:[],
				videoSrc:'',
				dotStyle: true,
				towerStart: 0,
				direction: '',
				isfollow:false,
				thumbNum:20,
				isThumb:false,
				iscomment:false,
				InputBottom: 0,
				detailDatas:[],
				commentLists:[],
				id:'',
				user:[],
				user_id:0,
				loading:true,
				
				emojiIcon:'cuIcon-emoji',
				isShowEmj:false,
				inputValue:'',
				commentary:'',
				focusComment:false,
				tagOptions:[],
				comment_id:'',
				parent_id:'',
				replyUserName:'',
				loadFinish:false//是否加载完接口数据
			};
		},
		mounted() {
			//this=this;
		},
		onShow() {
		},
		onLoad(e) {
			console.log("e: ",e);
			this.id=e.id;//19//
			this.loadTagOptions();
			this.detailDataLists();
			//评论列表
			this.showCommentListsDataOp();
			//this.attentionDataOp();
		},
		methods: {
			loadTagOptions(){
				this.$api.typeData(
				{type:0},
				data => {
					if (data.code == 1) {
						const res = Array.isArray(data.data) ? data.data : [];
						this.tagOptions = res.filter(item => String(item.id) !== '0');
					}
				}
				)
			},
			lookImg(index,type){
				if(type=='image'){
					let imgs=[]
					this.swiperList.map((item,index)=>{
						if(item.type=='image'){
							imgs[index]=item.url
						}
					})
					uni.previewImage({ 
						urls:imgs,
						current:index
					})
				}
			},
			//作品评论列表
			doCommentListsDataOp(type){
					this.$api.doCommentListsData(
					{page:1,limit:20},
					data => {
						console.log(data);
						var res=data.data;
						if (data.code == 1) {
							this.$common.normalToShow(data.msg);
							
						}
					}
					)  
			},
			//详情数据
			detailDataLists(){
				  this.user = this.$common.userInfo();
					this.loadFinish=false;
					this.swiperList=[];
					this.videoSrc='';
					//如果登录了，就要把当前的点赞状态给显示出来。
					if(this.user){
						this.user_id=this.user.id;
					}
					this.$api.detailData(
					{id:this.id,user_id:this.user_id},
					data => {
						console.log('加载接口成功',data);
						var res=data.data;
						if (data.code == 1) {
							this.loadFinish=true;
							 this.detailDatas=	res;
							 let swiperList=Array.isArray(res.coverPicVideos) ? res.coverPicVideos : [];
							 swiperList.map((item,index)=>{
								 if(item.type=='video'){
									 this.videoSrc=item.url
								 }
								 if(item.type=='image'){
									 this.swiperList.push(item)
								 }
							 })
							 // let videoIndex=swiperList.indexOf('.mp4')
							 // console.log('videoIndex',videoIndex);
							 
							 console.log('this.swiperList',this.swiperList);
							 if(res.isFavor==1){
								 //已点赞
								 this.isThumb=true;
							 }else{
								 this.isThumb=false; 
							 }
							 if(res.isAttention==1){
								 //已关注
								 this.isfollow=true;
							 }else{
								this.isfollow=false; 
							 }
						 
						}else{
							this.$common.normalToShow(data.msg,function(){
								this.$common.navigateTo('index')
							});
						}
					}
					)  
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
					var index=e.currentTarget.dataset.index;
					var id=this.id;
					if(type==2){
						comment_id= e.currentTarget.dataset.commentid;
					}
					console.log("comment_id: ",comment_id);
					if(this.loading){
						this.loading=false;
						this.$api.dolikeData(
						{type:type,discover_id:id,comment_id:comment_id},
						data => {
							console.log(data);
							var res=data.data;
							 this.loading=true;
							if (data.code == 1) {
								this.$common.normalToShow(data.msg);
								if(type==1){
									this.isThumb=!this.isThumb;
									this.detailDatas.favorNum=res.favorNum;
								}else{
									this.commentLists[index].isfavor=!this.commentLists[index].isfavor;
									this.commentLists[index].favorCount=res.favorNum;
									
								}
								
								
							}
						}
						)  
					}
					
			},
			//关注
			doAttentionDataOp(){
					this.$api.doAttentionData(
					{attention_id:this.detailDatas.user_id,discover_id:this.id},
					data => {
						console.log(data);
						if (data.code == 1) {
						  this.isfollow=!this.isfollow;
						  this.$common.successToShow(data.msg);	
						}else{
							this.$common.errorToShow(data.msg);	
						}
					}
					)  
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

			//评论列表
			showCommentListsDataOp(){
					this.user = this.$common.userInfo();
					//如果登录了，就要把当前的点赞状态给显示出来。
					if(this.user){
						this.user_id=this.user.id;
					}
					this.$api.showCommentListsData(
					{page:1,limit:10,discover_id:this.id,user_id:this.user_id},
					data => {
						var res=data.data;
						if (data.code == 1) {
							//处理回复内容content值（表情符号）
							this.commentLists=res.list.map(item=>{
								if(item.replyLists.length>0){
									item.replyLists=item.replyLists.map(items=>{
										return{
											...items,
											content:this.handleImg(items.content)	
										}
									})
								}
								//处理评论内容content值（表情符号）
								return{
									...item,
									content:this.handleImg(item.content)
								}
							})
						}
					}
					)  
			},	
			
			//轮播图当前位置放大效果
			cardSwiper(e){
				this.cardCur=e.detail.current;
			},
			
			//表情事件
			InputBlur(e) {
				this.InputBottom = 0
			},
			IsCard(e) {
				this.isCard = e.detail.value
			},
			showModal(status){
				if(status){
					this.isfollow=false;
				}else{
					this.isfollow=true;
				}
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
			
			changeInput(e){
				this.inputValue=e.detail.value;
			},
			handleEmj(i) {
				if(i == '[em_98]') {
					//匹配最后一个表情符号并删除。
					this.inputValue = this.inputValue.replace(/(\[[^\]]+\]|[\s\S])$/, '');
				} else {
					this.inputValue += i;
				}
			},
			//清除回复对象，直接对作品评论
			cleanReplyer(){
				this.comment_id=0;
				this.parent_id=0;
				this.focusComment=false;
			},
			//点击评论内容回复
			replyBtn(id,parent_id,userName){
				if(this.detailDatas.iscommentdata=='0'){
					this.$common.normalToShow('该条动态作者已关闭评论功能');
					return false;
				}
				console.log("parent_id: ",parent_id);
				this.comment_id=id;
				this.parent_id=parent_id;
				this.focusComment=true;
				this.replyUserName=userName
			},
			//作品评论/回复评论
			transmission(){
				if(this.inputValue){
					const parm={
					
					}
					//作品id
					parm['discover_id']=this.id;
					parm['parent_id']=this.parent_id;
					parm['content']=this.inputValue;
					//判断评论作品还是回复评论
					if(this.comment_id){
						//回复评论
						parm['comment_id']=this.comment_id;
						parm['type']=2;
					}else{
						//评论作品
						parm['type']=1;
					}
					this.$api.doCommentData(
					parm,
					data => {
						var res=data.data;
						//this.loading=true;
						if (data.code == 1) {
							//失去焦点
							this.focusComment=false;
							//收起表情
							this.isShowEmj=false;
							//情况输入框
							this.inputValue='';
							this.parent_id=0;
							this.comment_id=0;
							this.detailDatas.commentNum=data.data.commentNum;
							this.$common.successToShow(data.msg)
							this.showCommentListsDataOp();
						}
					}
					)  
				}else{
					uni.showToast({
						title: '评论内容不能为空',
						icon:'none',
						duration: 1000
					})
				}
				
			},
			
			
			showEmj() {
				let bool = !this.isShowEmj;
				if(bool) {
					this.emojiIcon = 'cuIcon-keyboard';
				} else {
					this.emojiIcon = 'cuIcon-emoji';
				}
				
				this.isShowEmj = bool;
				this.$emit('show')
			},
			InputFocus(e){
				this.isShowEmj = false;
				this.$emit('foc')
			},
			
			
		}
	}
</script>

<style lang="scss">
.detail-page{
	min-height: 100vh;
	background: #f6f8fc;
	--detail-accent: var(--school-theme-primary);
	--detail-accent-soft: var(--school-theme-secondary);
	--detail-accent-text: var(--school-theme-text);
}

.detail-page-body{
	padding: 24rpx;
}

.detail-post-card,
.detail-comment-section{
	background: #ffffff;
	border-radius: 28rpx;
	box-shadow: 0 12rpx 36rpx rgba(15, 23, 42, 0.06);
}

.detail-post-card{
	padding: 28rpx;
}

.detail-post-head{
	display: flex;
	align-items: center;
	justify-content: space-between;
}

.detail-post-user{
	flex: 1;
	min-width: 0;
	display: flex;
	align-items: center;
	padding-right: 20rpx;
}

.detail-post-avatar{
	width: 84rpx;
	height: 84rpx;
	border-radius: 24rpx;
	flex-shrink: 0;
	background: #e2e8f0;
}

.detail-post-user-main{
	min-width: 0;
	padding-left: 18rpx;
}

.detail-post-name{
	font-size: 32rpx;
	font-weight: 600;
	line-height: 1.3;
	color: #0f172a;
	word-break: break-all;
}

.detail-post-time{
	margin-top: 8rpx;
	font-size: 22rpx;
	line-height: 1.2;
	color: #94a3b8;
}

.detail-follow-btn{
	min-width: 144rpx;
	height: 64rpx;
	padding: 0 24rpx !important;
	border-radius: 999rpx;
	display: inline-flex;
	align-items: center;
	justify-content: center;
	color: var(--detail-accent) !important;
	background: #ffffff !important;
	border: 1rpx solid var(--detail-accent);
	box-sizing: border-box;
}

.detail-follow-btn text{
	font-size: 24rpx;
	line-height: 1;
	white-space: nowrap;
}

.detail-follow-btn text:first-child{
	margin-right: 8rpx;
}

.detail-follow-btn.is-active{
	color: #ffffff !important;
	background: linear-gradient(135deg, var(--detail-accent) 0%, var(--detail-accent-soft) 100%) !important;
	border-color: transparent;
}

.detail-post-title{
	margin-top: 26rpx;
	font-size: 34rpx;
	font-weight: 600;
	line-height: 1.45;
	color: #0f172a;
	word-break: break-word;
}

.detail-post-text{
	margin-top: 20rpx;
	font-size: 30rpx;
	line-height: 1.7;
	color: #1e293b;
	word-break: break-word;
	white-space: pre-wrap;
}

.detail-post-video{
	margin-top: 24rpx;
	border-radius: 24rpx;
	overflow: hidden;
	background: #0f172a;
}

.detail-post-video video{
	width: 100%;
	height: 420rpx;
	display: block;
}

.detail-post-media-grid{
	margin-top: 24rpx;
	display: grid;
	grid-template-columns: repeat(3, minmax(0, 1fr));
	gap: 12rpx;
}

.detail-post-media-item{
	height: 200rpx;
	border-radius: 20rpx;
	overflow: hidden;
	background: #e2e8f0;
}

.detail-post-media-item.is-single{
	grid-column: 1 / -1;
	height: 380rpx;
}

.detail-post-media-item image{
	width: 100%;
	height: 100%;
	display: block;
}

.detail-post-stats{
	margin-top: 28rpx;
	padding-top: 22rpx;
	border-top: 1rpx solid #eef2f7;
	display: flex;
	align-items: center;
	justify-content: space-between;
	gap: 20rpx;
}

.detail-post-tag{
	max-width: 48%;
	padding: 6rpx 14rpx;
	border-radius: 999rpx;
	background: #ffffff;
	border: 1rpx solid var(--detail-accent);
	display: inline-flex;
	align-items: center;
	font-size: 18rpx;
	color: var(--detail-accent);
	line-height: 1;
}

.detail-post-tag text{
	white-space: nowrap;
}

.detail-post-tag text:first-child{
	margin-right: 6rpx;
	font-size: 18rpx;
}

.detail-post-tag text:last-child{
	overflow: hidden;
	text-overflow: ellipsis;
}

.detail-post-stats-right{
	display: flex;
	align-items: center;
	justify-content: flex-end;
	flex: 1;
	min-width: 0;
}

.detail-post-stat{
	display: inline-flex;
	align-items: center;
	margin-left: 28rpx;
	font-size: 24rpx;
	color: #64748b;
}

.detail-post-stat:first-child,
.detail-comment-action:first-child{
	margin-left: 0;
}

.detail-post-stat text:first-child{
	margin-right: 8rpx;
	font-size: 28rpx;
}

.is-highlight{
	color: #ef4444 !important;
}

.detail-comment-section{
	margin-top: 24rpx;
	padding: 28rpx;
}

.detail-section-head{
	display: flex;
	align-items: center;
	justify-content: space-between;
	padding-bottom: 24rpx;
	border-bottom: 1rpx solid #eef2f7;
}

.detail-section-title{
	font-size: 30rpx;
	font-weight: 600;
	color: #0f172a;
}

.detail-section-note{
	font-size: 22rpx;
	color: #94a3b8;
}

.detail-comment-card{
	display: flex;
	align-items: flex-start;
	padding: 28rpx 0;
	border-bottom: 1rpx solid #f1f5f9;
}

.detail-comment-card:last-child{
	border-bottom: none;
	padding-bottom: 0;
}

.detail-comment-avatar{
	width: 64rpx;
	height: 64rpx;
	border-radius: 18rpx;
	flex-shrink: 0;
	background: #e2e8f0;
}

.detail-comment-main{
	flex: 1;
	min-width: 0;
	padding-left: 18rpx;
}

.detail-comment-name{
	font-size: 26rpx;
	font-weight: 500;
	line-height: 1.3;
	color: #334155;
}

.detail-comment-text{
	margin-top: 12rpx;
	font-size: 28rpx;
	line-height: 1.65;
	color: #0f172a;
	word-break: break-word;
}

.detail-comment-replies{
	margin-top: 18rpx;
	padding: 20rpx;
	border-radius: 20rpx;
	background: #f8fafc;
}

.detail-comment-reply + .detail-comment-reply{
	margin-top: 18rpx;
	padding-top: 18rpx;
	border-top: 1rpx solid #e2e8f0;
}

.detail-comment-reply-head{
	display: flex;
	align-items: flex-start;
	justify-content: space-between;
	gap: 16rpx;
}

.detail-comment-reply-user{
	flex: 1;
	min-width: 0;
	font-size: 22rpx;
	line-height: 1.4;
	color: #64748b;
}

.reply-user-name{
	color: var(--detail-accent);
}

.reply-user-target{
	color: #475569;
}

.detail-comment-reply-time{
	flex-shrink: 0;
	font-size: 20rpx;
	line-height: 1.4;
	color: #94a3b8;
}

.detail-comment-reply-text{
	display: block;
	margin-top: 12rpx;
	font-size: 24rpx;
	line-height: 1.7;
	color: #334155;
	word-break: break-word;
}

.detail-comment-foot{
	margin-top: 18rpx;
	display: flex;
	align-items: center;
	justify-content: space-between;
}

.detail-comment-time{
	font-size: 20rpx;
	color: #94a3b8;
}

.detail-comment-actions{
	display: flex;
	align-items: center;
}

.detail-comment-action{
	display: inline-flex;
	align-items: center;
	margin-left: 24rpx;
	font-size: 22rpx;
	color: #64748b;
}

.detail-comment-action text:first-child{
	margin-right: 6rpx;
	font-size: 26rpx;
}

.detail-empty-state{
	padding: 72rpx 0 56rpx;
	text-align: center;
}

.detail-empty-icon{
	width: 120rpx;
	height: 120rpx;
	margin: 0 auto;
	border-radius: 50%;
	background: linear-gradient(135deg, var(--detail-accent-soft) 0%, #ffffff 100%);
	display: flex;
	align-items: center;
	justify-content: center;
}

.detail-empty-icon text{
	font-size: 56rpx;
	color: var(--detail-accent);
}

.detail-empty-title{
	margin-top: 24rpx;
	font-size: 30rpx;
	font-weight: 600;
	color: #334155;
}

.detail-empty-text{
	margin-top: 12rpx;
	font-size: 24rpx;
	color: #94a3b8;
}

.detail-loading{
	padding: 120rpx 0;
	font-size: 26rpx;
	text-align: center;
	color: #94a3b8;
}

.detail-bottom-space{
	height: 152rpx;
}

.detail-comment-bar{
	position: fixed;
	left: 0;
	right: 0;
	bottom: 0;
	padding: 16rpx 20rpx calc(16rpx + env(safe-area-inset-bottom));
	background: rgba(255, 255, 255, 0.98);
	border-top: 1rpx solid #e2e8f0;
	box-shadow: 0 -12rpx 36rpx rgba(15, 23, 42, 0.06);
	z-index: 20;
}

.detail-reply-tip{
	margin-bottom: 16rpx;
	padding: 0 8rpx;
	display: flex;
	align-items: center;
	justify-content: space-between;
}

.detail-reply-tip-text{
	display: flex;
	align-items: center;
	font-size: 22rpx;
}

.detail-reply-tip-label{
	color: #94a3b8;
}

.detail-reply-tip-name{
	margin-left: 8rpx;
	color: var(--detail-accent);
}

.detail-comment-bar-main{
	display: flex;
	align-items: center;
}

.detail-comment-input{
	flex: 1;
	height: 76rpx;
	padding: 0 24rpx;
	border-radius: 999rpx;
	background: #f8fafc;
	border: 1rpx solid #e2e8f0;
	display: flex;
	align-items: center;
}

.detail-comment-input input{
	width: 100%;
	font-size: 26rpx;
	color: #0f172a;
}

.detail-comment-emoji{
	width: 76rpx;
	height: 76rpx;
	margin-left: 16rpx;
	border-radius: 50%;
	background: #ffffff;
	border: 1rpx solid var(--detail-accent);
	display: flex;
	align-items: center;
	justify-content: center;
	color: var(--detail-accent);
	font-size: 34rpx;
	flex-shrink: 0;
}

.detail-comment-submit{
	height: 76rpx;
	margin-left: 16rpx;
	padding: 0 28rpx !important;
	border-radius: 999rpx;
	color: #ffffff !important;
	background: linear-gradient(135deg, var(--detail-accent) 0%, var(--detail-accent-soft) 100%) !important;
	box-shadow: 0 12rpx 24rpx rgba(15, 23, 42, 0.10);
}
</style>

