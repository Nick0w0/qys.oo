<template>
	<view>
		
			<cu-custom bgColor="bg-gradual-purple">
				<block slot="backText">返回</block>
				<block slot="content">消息列表</block>
			</cu-custom>
			<scroll-view scroll-x class="bg-gradual-purple nav text-center">
				<view class="cu-item" :class="0==TabCur?'text-white cur':''" @tap="tabSelect" data-id="0">
					<text class="cuIcon-all"></text>  全部
				</view>
				<view class="cu-item" :class="1==TabCur?'text-white cur':''" @tap="tabSelect" data-id="1">
					<text class="cuIcon-favorfill"></text> 点赞
				</view>
				<view class="cu-item" :class="2==TabCur?'text-white cur':''" @tap="tabSelect" data-id="2">
					<text class="cuIcon-attentionfavorfill"></text> 关注
				</view>
				<view class="cu-item" :class="3==TabCur?'text-white cur':''" @tap="tabSelect" data-id="3">
					<text class="cuIcon-commentfill"></text> 评论
				</view>
			</scroll-view>
			<view class="cu-bar bg-white solid-bottom ">
				<view class="action">
					<text class="cuIcon-title text-orange "></text> 全部消息
				</view>
				<view class="action">
					<button class="cu-btn bg-grey shadow" @tap="showModal" data-target="gridModal">设为已读</button>
				</view>
			</view>
			
			<view class="cu-list menu-avatar">
				<view class="cu-item"   v-for="(item,index) in messageLists" :key="index" :data-type='item.typedata' :data-discover_id='item.discover_id' :data-id='item.id' :data-index='index' :data-user_id='item.user_id' @click="messageClick">
					<view class="cu-avatar round lg" v-if="item.avatar!=''" :style="{'background-image':'url('+item.avatar+')'}"></view>
					<view class="cu-avatar round lg" v-else style="background-image: url(../../static/images/avatar.png);"></view>
					<block v-if="item.typedata==4"><!--消息类型:1=作品被点赞,2=评论被点赞,3=作品被评论,4=被关注,5=评论被回复,6=备用-->
						<view class="content">
							<view class="text-grey">{{item.nickname}}</view>
							<view class="text-gray text-sm flex">
								<view class="text-cut">
									<text class="margin-right-xs"  :class="item.readdata==0?'cuIcon-noticefill text-red':'cuIcon-roundcheckfill text-green'"></text>
									{{item.content}}
								</view> </view>
						</view>
						<view class="action w80">
							<view class="text-grey text-xs">{{item.createtime}}</view>
							<!-- <view class="cu-tag round sm">22:20</view> -->
						</view>
					</block>
					<block v-else>
						<view class="content">
							<view class="text-grey">
								<view class="text-cut">{{item.nickname}}</view>
								<!-- <view class="cu-tag round bg-orange sm">战士</view> -->
							</view>
							<view class="text-gray text-sm flex">
								<view class="text-grey text-xs margin-right-xs"><text class="margin-right-xs" :class="item.readdata==0?'cuIcon-noticefill text-red':'cuIcon-roundcheckfill text-green'"></text>{{item.createtime}}</view> 
								<view class="text-cut">
									{{item.content}}
								</view>
							</view>
						</view>
						<view class="action">
							<view class="cu-avatar radius lg"  :style="{'background-image':'url('+item.coverimage+')'}"></view>
						</view>
					</block>
					
					
				</view>
				
				<!-- <view class="cu-item ">
					<view class="cu-avatar round lg" style="background-image:url(https://ossweb-img.qq.com/images/lol/img/champion/Morgana.png);"></view>
					<view class="content">
						<view class="text-pink"><view class="text-cut">莫甘娜</view></view>
						<view class="text-gray text-sm flex"><view class="text-grey text-xs margin-right-xs">2021-05-20 22:20</view> <view class="text-cut">评论了你的作品</view></view>
					</view>
					<view class="action">
						<view class="cu-avatar radius lg" style="background-image:url(https://ossweb-img.qq.com/images/lol/img/champion/Morgana.png);"></view>
					</view>
				</view> -->
				
			</view>
			<view class="text-center text-gray padding-tb-sm" :class="showNoResult?'show':'hide'">我是有底线的...</view>
		<colorbar footerTab="3" :messageCount='messageCount'></colorbar>
	</view>
</template>

<script>
	var page=1;var _this;
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
				user:[]
				
			};
		},
		mounted() {
			_this=this;
		},
		onLoad() {
			page=1;
		},
		onShow() {
			page=1;
			
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
			this.showMessageListsDataOp();
		},
		methods: {
		//消息列表
		showMessageListsDataOp(){
					this.$api.showMessageListsData(
					{page:page,limit:10,type:this.type},
					data => {
						console.log(data);
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
							console.log("this.ids: ",this.ids);
						}else{
							this.$common.errorToshow(data.msg);
						}
					}
					)  
			},
		//消息点击事件
		messageClick(e){
				var discover_id=e.currentTarget.dataset.discover_id;
				var id=e.currentTarget.dataset.id;
				console.log("discover_id: ",discover_id);
				var typedata=e.currentTarget.dataset.type;
				var user_id=e.currentTarget.dataset.user_id;
				var index=e.currentTarget.dataset.index;
				console.log("index: ",index);
				this.doMessageReadDataOp(id,index);
				if(typedata!=4){
					this.$common.navigateTo('../index/detail?id='+discover_id);
				}
				if(typedata==4){
					this.$common.navigateTo('../index/user?id='+user_id);
				}
			},
	    //消息已读
	    doMessageReadDataOp(id,index){
			
			if(_this.ids!=''){
				if(id){
				 var pid=id;	
				}else{
				 var pid=_this.ids
				}
				console.log("pid: ",pid);
				_this.$api.doMessageReadData(
				{ids:pid},
				data => {
					console.log(data);
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
						_this.$common.errorToShow(data.msg);}
					}
				}
				)  
			}else{
				if(!id){
				_this.$common.errorToshow('暂无未读消息');}
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
			this.showMessageListsDataOp();
		}

		}
	}
</script>

<style>
	.page {
		height: 100Vh;
		width: 100vw;
	}
    .w80{width: 160rpx !important;}
	.page.show {
		overflow: hidden;
	}

	.switch-sex::after {
		content: "\e716";
	}

	.switch-sex::before {
		content: "\e7a9";
	}

	.switch-music::after {
		content: "\e66a";
	}

	.switch-music::before {
		content: "\e6db";
	}
	.show{ display: block;}
	.hide{ display: none;}
</style>
