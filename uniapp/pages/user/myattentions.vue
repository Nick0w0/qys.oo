<template>
	<view>
			<cu-custom bgColor="bg-gradual-purple" :isBack="true">
				<block slot="backText">返回</block>
				<block slot="content">{{name}}</block>
			</cu-custom>
			
			<view class="cu-list menu-avatar">
				<view class="cu-item" :class="modalName=='move-box-'+ index?'move-cur':''" v-for="(item,index) in userDataLists" :key="index"
				 @touchstart="ListTouchStart" @touchmove="ListTouchMove" @touchend="ListTouchEnd" :data-target="'move-box-' + index">
					<view class="cu-avatar round lg" :style="{'background-image':'url('+item.avatar+')'}"></view>
					<view class="content">
						<view class="text-grey">{{item.nickname}}</view>
						<view class="text-gray text-sm">
							 {{item.date}}</view>
					</view>
					<view class="action">
						<view class="text-grey text-xs">{{item.time}}</view>
						
					</view>
					<view class="move">
						<view class="bg-red" v-if="type==1" >移除</view>
						<view class="bg-red" v-if="type==2">取关</view>
						<view class="bg-red" v-if="type==3">关注</view>
					</view>
				</view>
			</view>
			<view class="text-center text-gray padding-tb-sm" :class="showNoResult?'show':'hide'">我是有底线的...</view>
	</view>
</template>

<script>
	var page=1;var _this;
	export default {
		data() {
			
			return {
				modalName: null,
				listTouchStart: 0,
				listTouchDirection: null,
				name:'列表',
				type:1,
				totalPage:'',
				showNoResult:false,
				userDataLists:[]
				
			};
		},
		mounted() {
			_this=this;
		},
		onLoad(option) {
			console.log(option);
			if(option.type){
				this.type=option.type;
				
				if(this.type==1){
					this.name="关注我的"
					uni.setNavigationBarTitle({
						title:"关注我的"
					})
				}
				if(this.type==2){
					this.name="我关注的"
					uni.setNavigationBarTitle({
						title:"我关注的"
					})
				}
				if(this.type==3){
					this.name="点赞我的"
					uni.setNavigationBarTitle({
						title:"点赞我的"
					})
				}
			}else{
				this.$common.errorToShow('参数错误',function(){
					uni.navigateBack();
				})
			}
			page=1;
			this.userDataLists=[];
			this.totalPage='';
			this.userAttentionDataOp();
			
		},
		onShow() {
			
		},
		onReachBottom() {
		if(this.totalPage>=page){
			this.userAttentionDataOp();
		}else{
			this.showNoResult=true
		}	
		},
		methods: {
			userAttentionDataOp(){
				if(this.type==1){
					this.$api.doAttentionListsData(
						{type:1,page:page,limit:10},
						data => {
							if (data.code == 1) {
								var res=data.data;
								if(res.list.length<10){
									_this.showNoResult=true;
									_this.$common.errorToShow('暂无更多数据');
								}
								page++;
								_this.userDataLists=_this.userDataLists.concat(res.list);
								_this.totalPage=res.total;
							}else{
							_this.$common.errorToShow(data.msg);
						    }
						}
					)  
				}
				if(this.type==2){
					this.$api.doAttentionListsData(
						{type:2,page:page,limit:10},
						data => {
							if (data.code == 1) {
								var res=data.data;
								if(res.list.length<10){
									_this.showNoResult=true;
								}
								page++;
								_this.userDataLists=_this.userDataLists.concat(res.list);
								_this.totalPage=res.total;
								
							}else{
							_this.$common.errorToShow(data.msg);
						    }
						}
					)  
				}
				if(this.type==3){
					this.$api.doLikeListsData(
						{page:page,limit:10},
						data => {
							
							if (data.code == 1) {
								console.log(data);
								var res=data.data;
								if(res.list.length<10){
									_this.showNoResult=true;
									_this.$common.errorToShow('暂无更多数据');
								}
								
								page++;
								_this.userDataLists=_this.userDataLists.concat(res.list);
								_this.totalPage=res.total;
							}else{
							_this.$common.errorToShow(data.msg);
						    }
						}
					)  
				}
				
				
			},

			// ListTouch触摸开始
			ListTouchStart(e) {
				this.listTouchStart = e.touches[0].pageX
			},

			// ListTouch计算方向
			ListTouchMove(e) {
				this.listTouchDirection = e.touches[0].pageX - this.listTouchStart > 0 ? 'right' : 'left'
			},

			// ListTouch计算滚动
			ListTouchEnd(e) {
				if (this.listTouchDirection == 'left') {
					this.modalName = e.currentTarget.dataset.target
				} else {
					this.modalName = null
				}
				this.listTouchDirection = null
			}
		}
	}
</script>

<style>
	.page {
		height: 100Vh;
		width: 100vw;
	}

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
</style>
