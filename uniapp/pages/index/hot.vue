<template>
	<view>
		<cu-custom bgColor=" bg-gradual-purple"><block slot="content">关注</block></cu-custom>

		<view class="cu-card dynamic" v-for="(item,index) in attentionDataLists" :key="index">
			<view class="cu-item shadow">
				<view class="cu-list menu-avatar">
					<view class="cu-item">
						<view class="cu-avatar round lg" v-if="item.avatar!=''" :style="{'background-image':'url('+item.avatar+')'}"  :data-id="item.id" @click="detail"></view>
						<view class="cu-avatar round lg" v-else style="background-image: url(../../static/images/avatar.png);"  :data-id="item.id" @click="detail"></view>
						<view class="content flex-sub"  :data-id="item.id" @click="detail">
							<view>{{item.nickname}}</view>
							<view class="text-gray text-sm flex justify-between">
								{{item.createtime}}
							</view>
						</view>
						<view class="action">
							<button class="cu-btn light sm" :data-id='item.id' :data-userid='item.user_id' @tap="doAttentionDataOp" :class="item.isattention?'bg-red':'bg-grey'" data-target="gridModal"><text class="cuIcon-attentionfavorfill" :class="item.isattention?'text-red':'text-grey'"></text></text></button> 
						</view>
					</view>
				</view>
				<view class="margin-top-sm text-black padding-sm" :data-id="item.id" @click="detail">
					{{item.content}}
				</view>
				<view class="grid flex-sub padding-lr grid-square" :class="item.coverimages.length>1?'col-3':'col-1'">
					<view class="bg-img" v-for="(item2,index2) in item.coverimages" :key="index2" :data-index='index' :data-index2="index2" :style="{'background-image':'url('+item2+')'}" @click="previewImage">
					</view>
				</view>
				<view class="grid justify-end text-gray text-sm padding solids-bottom">
					<text class="cuIcon-attentionfill margin-lr-xs"></text> {{item.browse}}
					<!--点赞-->
					<view data-type='1' :data-id='item.id' :data-index='index' data-index2='0' @click="dolikeDataOp">
						<text class="cuIcon-appreciatefill margin-lr-xs" :class="item.isfavor==1?'text-red':''"></text> {{item.favorNum}}
					</view>
					
					<text class="cuIcon-messagefill margin-lr-xs"  :data-id="item.id" @click="detail"></text> {{item.commentNum}}
				</view>

				<view class="cu-list menu-avatar comment solids-top" v-if="item.comment_lists.length>0"  :data-id="item.id" @click="detail">
					<view class="cu-item" v-for="(item3,index3) in item.comment_lists" :key="index3">
						<view class="cu-avatar round" v-if="item3.avatar!=''" :style="{'background-image':'url('+item3.avatar+')'}"></view>
						<view class="cu-avatar round" v-else style="background-image: url(../../static/images/avatar.png);"></view>
						
						<view class="content">
							<view class="text-grey">{{item3.nickname}}</view>
							<view class="text-gray text-content text-df">
								<!-- {{item3.content}} -->
								<rich-text :nodes="item3.content"></rich-text>
							</view>
							<view class="bg-text-grey margin-top-sm" v-if="item3.replyLists.length>0">
							<block v-for="(item4,index4) in item3.replyLists" :key="index4">
								<view class="text-greyradius text-sm">
									<view class="flex justify-between">
										<view><text class="text-purple">{{item4.rnickname}}</text>回复<text class="text-grey">{{item4.nickname}}</text>：
										</view>
										<text class="text-grey text-sm">{{item4.createtime}}</text>
									</view>
									<rich-text class="padding-tb-sm  block " :nodes="item4.content"></rich-text><!-- {{item2.content}} -->
								</view>
							</block>
							</view>
							<view class="margin-top-sm flex justify-between text-gray">
								<view class="text-gray text-df">{{item3.createtime}}</view>
								<view>
									<text class="cuIcon-appreciatefill" data-type='2' :data-id="item.id" :data-commentid='item3.id' :data-index='index' :data-index2='index3' :class="item3.isfavor==1?'text-red':'text-gray'" @click="dolikeDataOp"></text> {{item3.favorCount}}
									<text class="cuIcon-messagefill text-gray margin-left-sm"></text>{{item3.commentCount}}
								</view>
							</view>
						</view>
					</view>

				</view>
			</view>
		</view>
		<view class="text-center text-gray padding-tb-sm" :class="showNoResult?'show':'hide'">我是有底线的...</view>
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
				messageCount:0
				
			};
		},
		mounted() {
			_this=this;
		},
		onLoad() {
			page=1;
			this.attentionDataOp();
		},
		onShow() {
			this.user = this.$common.userInfo();
			console.log("this.user: ",this.user);
			if (typeof(this.user)== "undefined" || this.user=='' ||  this.user==null) {
				this.$common.navigateTo('login');
			}else{
				this.$api.refreshUser(
				{},
				val => {
					this.user=val.data.user;
					this.auth=val.data.auth;
					this.messageCount=val.data.msgCount;
				})
			}
		},
		onReachBottom() {
			if(this.totalPage>=page){
				this.attentionDataOp();
			}else{
				this.showNoResult=true
			}
		},
		methods: {
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
						var res=data.data;
						if(res.list.length<10){
							_this.showNoResult=true;
						}
						if (data.code == 1) {
							page++;
							_this.attentionDataLists=_this.attentionDataLists.concat(res.list);
							_this.attentionDataLists=_this.attentionDataLists.map(item=>{
								item.comment_lists=item.comment_lists.map(items=>{
									return {
										...items,
										content:this.handleImg(items.content)
									}
								})
								return{
									...item
								}
							})
							console.log(_this.attentionDataLists);
							_this.totalPage=res.total_page;
							
						}else{
							_this.$common.errorToShow(data.msg);
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
		}
	}
</script>

<style>

</style>
