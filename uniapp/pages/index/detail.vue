<template>
	<view>
		<cu-custom bgColor=" bg-gradual-purple" :isBack="true"><block slot="backText">返回</block><block slot="content">{{detailDatas.title}}</block></cu-custom>
		<view class="video" v-if="videoSrc">
			<video :src="videoSrc" autoplay loop muted :show-play-btn="true" :controls="true" objectFit="cover"></video>
		</view>
		<view class="image" v-if="swiperList.length>0">
			<swiper class="card-swiper" :class="dotStyle?'square-dot':'round-dot'" :indicator-dots="true" :circular="true"
			:autoplay="true"  interval="5000" duration="500" @change="cardSwiper" indicator-color="#8799a3"
			 indicator-active-color="#8300FF">
				<swiper-item v-for="(item,index) in swiperList" :key="index" :class="cardCur==index?'cur':''">
					<view class="swiper-item" @click="lookImg(index,item.type)">
						<image :src="item.url" mode="aspectFill"></image>
					</view>
				</swiper-item>
			</swiper>
		</view>
		
		 
		<view class="cu-card dynamic"  :class="isCard?'no-card':''" v-if="loadFinish">
			<view class="cu-item shadow">
				<view class="cu-list menu-avatar">
					<view class="cu-item">
						<view class="cu-avatar round lg" :style="{'background-image':'url('+detailDatas.avatar+')'}"></view>
						<view class="content flex-sub">
							<view>{{detailDatas.nickname}}</view>
							<view class="text-gray text-sm flex justify-between">
								{{detailDatas.createtime}}
							</view>
						</view>
						<view class="action">
							<button class="cu-btn light sm" @tap="doAttentionDataOp" :class="isfollow?'bg-red':'bg-grey'" data-target="gridModal"><text class="cuIcon-attentionfavorfill" :class="isfollow?'text-red':'text-grey'"></text></text></button>
						</view>
					</view>
				</view>
				<view class="margin-top-sm text-black padding-sm">
					{{detailDatas.content}}
				</view>
				<view class="grid justify-end text-gray text-sm padding solids-bottom">
					<text class="cuIcon-attentionfill margin-lr-xs"></text> {{detailDatas.browse}}
					<!--点赞-->
					<view data-type='1' @click="dolikeDataOp">
						<text class="cuIcon-appreciatefill margin-lr-xs" :class="isThumb?'text-red':''"></text> {{detailDatas.favorNum}}
					</view>
					
					<text class="cuIcon-messagefill margin-lr-xs"></text> {{detailDatas.commentNum}}
				</view>
				 <view class="cu-bar bg-white margin-top">
				 	<view class="action">
				 		<text class="cuIcon-title text-purple"></text>
				 		<text>评论列表</text>
				 	</view>
				 </view>

					<view  v-if="commentLists.length>0">
					<view class="cu-list menu-avatar comment"  v-for="(item,index) in commentLists" :key="index">
						<view class="cu-item">
							<view class="cu-avatar round" v-if="item.avatar!=''" :style="{'background-image':'url('+item.avatar+')'}"  @click="replyBtn(item.id,item.id,item.nickname)"></view>
							<view class="cu-avatar round"v-else style="background-image: url(../../static/images/avatar.png);" @click="replyBtn(item.id,item.id,item.nickname)"></view>
							<view class="content">
								<view class="text-grey text-sm"  @click="replyBtn(item.id,item.id,item.nickname)">{{item.nickname}}</view>
								<view class="text-black text-content text-df" @click="replyBtn(item.id,item.id,item.nickname)">
									<rich-text :nodes="item.content"></rich-text> <!-- {{item.content}} -->
								</view>
								<view class="bg-text-grey margin-top-sm" v-if="item.replyLists.length>0">
								<block v-for="(item2,index2) in item.replyLists" :key="index2">
									<view class="text-greyradius text-sm"  @click="replyBtn(item2.id,item.id,item2.rnickname)">
										<view class="flex justify-between">
											<view><text class="text-purple">{{item2.rnickname}}</text>回复<text class="text-grey">{{item2.nickname}}</text>：
											</view>
											<text class="text-grey text-sm">{{item2.createtime}}</text>
										</view>
										<rich-text class="padding-tb-sm  block " :nodes="item2.content"></rich-text><!-- {{item2.content}} -->
									</view>
								</block>
								</view>
								<view class="margin-top-sm flex justify-between text-gray">
									<view class="text-gray text-df">{{item.createtime}}</view>
									<view>
										<text class="cuIcon-appreciatefill" data-type='2' :data-commentid='item.id' :data-index='index' :class="item.isfavor==1?'text-red':'text-gray'" @click="dolikeDataOp"></text> {{item.favorCount}}
										<text class="cuIcon-messagefill text-gray margin-left-sm"></text>{{item.commentCount}}
									</view>
								</view>
							</view>
						</view>
					</view> 
					</view>
					<view class="text-center text-gray padding"  v-else>暂无更多评论...</view>

				
				<view class="text-center text-gray padding"  v-else>暂无更多评论...</view>
				
			</view>
		</view>
<!-- 		<view style="height: 100rpx;"></view>
		<view class="cu-bar foot input" :style="[{bottom:InputBottom+'px'}]">
			<view class="action">
				<text class="cuIcon-sound text-grey"></text>
			</view>
			<input class="solid-bottom" :adjust-position="false" :focus="false" maxlength="300" cursor-spacing="10"
			 @focus="InputFocus" @blur="InputBlur"></input>
			<view class="action">
				<text class="cuIcon-emojifill text-grey"></text>
			</view>
			<button class="cu-btn bg-green shadow">发送</button>
		</view> -->	
		<view style="height: 90rpx;"></view>
		<view class="bg-white emj_box" v-if="detailDatas.iscommentdata=='1'">
			<view class="flex justify-between padding-tb-sm"  v-if="focusComment">
				<view><text class="text-gray">回复：</text><text class="text-purple">{{replyUserName}}</text></view>
				<text class="cuIcon-close" @click="cleanReplyer"></text>
			</view>
			<view class="flex">
				<view class="flex-sub padding-left-xs" style="align-self: center;">
					<input type="text" @input="changeInput" @focus="InputFocus" @blur="InputBlur" :value="inputValue" :focus="focusComment" placeholder-style="font-size:24rpx;color:#aaaaaa;"  placeholder="直接输入评论或点击评论回复TA" maxlength="300"></input>
				</view>
				
				<view class="text-center" style="width: 100rpx; font-size: 50rpx;">
					<text :class="emojiIcon" @tap="showEmj"></text>
				</view>
				<button class="cu-btn bg-gradual-blue shadow-blur" @click="transmission('')">发表评论</button>
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
			this.detailDataLists();
			//评论列表
			this.showCommentListsDataOp();
			//this.attentionDataOp();
		},
		methods: {
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
							 let swiperList=res.coverPicVideos;
							 swiperList.map((item,index)=>{
								 if(item.url.includes('.mp4')){
									 //console.log('视频index值',item);
									 this.videoSrc=item.url
								 }
								 if(item.url.includes('.jpg')||item.url.includes('.jpeg')||item.url.includes('.png')){
								 	 //console.log('图片index值',item);
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
					this.$common.normalToShow('改条动态作者已关闭评论功能');
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
.bg-text-grey{ background: #f7f7f7; padding: 10rpx 20rpx; color: #666;}
.emj_box {
		padding: 15rpx;
		position:fixed; width:100%;
		margin:0 auto; left:0;
		right:0;  bottom:0; 
		border-top: 1px solid #EEEEEE;
	}
	.video{
		video{width: 100%;display: block;}
	}
</style>
