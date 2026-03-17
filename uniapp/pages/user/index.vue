<template>
	<view :style="themeVarsStyle">
		<scroll-view :scroll-y="modalName==null" class="page" :class="modalName!=null?'show':''">
			<cu-custom bgColor="bg-gradual-purple">
				<block slot="backText">返回</block>
				<block slot="content">我的</block>
			</cu-custom>
			<view class="cu-list menu-avatar" v-if="user">
				<view class="cu-item bg-gradual-purple" @click="userInfo">
					<view class="cu-avatar round lg" :style="{'background-image':'url('+user.avatar+')'}">
						<!-- <view class="cu-tag badge" :class="user.gender==0?'cuIcon-female bg-pink':'cuIcon-male purple'"></view> -->
					</view>
					<view class="content flex-sub">
						<view class="text-white  text-sm flex justify-between">{{user.nickname}}
							<view class="text-white text-sm">
								<view @click.stop="logout">
										  退出 <text class="cuIcon-right"></text>
								</view>
							</view>
						</view>
						<view class="text-white text-sm flex justify-between">
							{{user.bio}}
							<view class="text-white text-sm">
								<text class="cuIcon-appreciatefill margin-lr-xs"></text> {{myUserData.favorListsCount}}
								<!-- <text class="cuIcon-messagefill margin-lr-xs"></text> 30 -->
							</view>
						</view>
					</view>
				</view>
			</view>
			<view class="header padding flex_layout" @click="loginGo" v-else>
				<view class="img"><image src="@/static/images/avatar.png"></image></view>
				<view class="right padding-left">
					<view class="flex_layout justify-between">
						<view class="name">请先登录</view>
						<view class="icon"><text class="cuIcon-right"></text></view>
					</view>
				</view>
			</view>
	
			<view class="cu-list menu" :class="[menuBorder?'sm-border':'',menuCard?'card-menu margin-top':'']">

				<view class="cu-item" :class="menuArrow?'arrow':''"  @click="userListClick(1)">
					<view class="content">
						<text class="cuIcon-attentionfavorfill text-purple"></text>
						<text class="text-grey">关注我的</text>
					</view>
					<view class="action">
						<view class="cu-avatar-group">
							<view class="cu-avatar round sm" v-if="item.avatar!=''" :style="{'background-image':'url('+item.avatar+')'}"  v-for="(item,index) in myUserData.beAttentionLists" :key="index"></view>
							<view class="cu-avatar round sm" v-else style="background-image: url(../../static/images/avatar.png);"  v-for="(item,index) in myUserData.beAttentionLists" :key="index"></view>
							  
						</view>
						<text class="text-grey text-sm">{{myUserData.beAttentionListsCount}} 人</text>
					</view>
				</view>
				<view class="cu-item" :class="menuArrow?'arrow':''" @click="userListClick(2)">
					<view class="content">
						<text class="cuIcon-friendaddfill text-orange"></text>
						<text class="text-grey">我关注的</text>
					</view>
					<view class="action">
						<view class="cu-avatar-group">
							<view class="cu-avatar round sm" v-if="item.avatar!=''" :style="{'background-image':'url('+item.avatar+')'}"  v-for="(item,index) in myUserData.attentionLists" :key="index"></view>
							<view class="cu-avatar round sm" v-else style="background-image: url(../../static/images/avatar.png);"  v-for="(item,index) in myUserData.attentionLists" :key="index"></view>
						</view>
						<text class="text-grey text-sm">{{myUserData.attentionListsCount}} 人</text>
					</view>
				</view>
				
				<view class="cu-item" :class="menuArrow?'arrow':''"  @click="userListClick(3)">
					<view class="content">
						<text class="cuIcon-emojiflashfill text-pink"></text>
						<text class="text-grey">点赞我的</text>
					</view>
					<view class="action">
						<view class="cu-avatar-group">
							<view class="cu-avatar round sm" v-if="item.avatar!=''" :style="{'background-image':'url('+item.avatar+')'}"  v-for="(item,index) in myUserData.favorLists" :key="index"></view>
							<view class="cu-avatar round sm" v-else style="background-image: url(../../static/images/avatar.png);"  v-for="(item,index) in myUserData.favorLists" :key="index"></view>
						</view>
						<text class="text-grey text-sm">{{myUserData.favorListsCount}} 次</text>
					</view>
				</view>
			
				
				<view class="cu-item" :class="menuArrow?'arrow':''" @tap="showModal" data-target="ChooseModal" style="display: none;">
					<view class="content">
						<text class="cuIcon-tagfill text-red  margin-right-xs"></text>
						<text class="text-grey">我的标签</text>
					</view>
					<view class="action">
						<block v-for="(item,index) in labelList" :key="index">
							<block v-if="index==0">
								<view class="cu-tag round bg-orange light">{{item.name}}</view>
							</block>
							<block v-if="index==1">
								<view class="cu-tag round bg-olive light">{{item.name}}</view>
							</block>
							<block v-if="index==2">
								<view class="cu-tag round bg-blue light">{{item.name}}</view>
							</block>
						</block>
						<block v-if="labelList.length>3">
							<view class="cu-tag bg-white">...</view>
						</block>
						
					</view>
					<!-- <view class="action">
						<text class="text-grey text-sm">添加属于你的标签！</text>
					</view> -->
				</view>
				
				<!--我的标签弹出窗-->
				<view class="cu-modal bottom-modal" :class="modalName=='ChooseModal'?'show':''" @tap="hideModal">
					<view class="cu-dialog" @tap.stop="">
						<view class="cu-bar bg-white">
							<view class="action text-grey" @tap="hideModal">取消</view>
							<view class="action text-purple" @tap="confirmBtn">确定</view>
						</view>
						<view class="grid col-3 padding-sm">
							<view v-for="(item,index) in checkbox" class="padding-xs" :key="index">
								<button class="cu-btn round" :class="item.checked?'bg-purple':'line-grey'" @tap="ChooseCheckbox"
								 :data-value="item.value"> {{item.name}}
									<view class="cu-tag sm round" :class="item.checked?'bg-white text-purple':'bg-grey'" v-if="item.hot">HOT</view>
								</button>
							</view>
						</view>
					</view>
				</view>
				
				<view class="publish-section" v-if="user">
					<view class="publish-header">
						<view class="publish-title">
							<text class="publish-dot"></text>
							<text>我发布的</text>
						</view>
						<view class="publish-total">{{total || 0}}</view>
					</view>
					<block v-if="discoverLists.length>0">
						<view class="publish-group" v-for="(item,index) in discoverLists" :key="index">
							<view class="publish-date">{{item.monthDate}}</view>
							<view class="publish-card">
								<view class="publish-item" v-for="(item2,index2) in item.list" :key="index2">
									<view class="publish-main" @click="detail(item2.id)">
										<view class="publish-content">
											<view class="publish-text">{{item2.title}}</view>
											<view class="publish-meta">
												<text>{{item2.time}}</text>
												<text><text class="cuIcon-appreciatefill"></text>{{item2.favorNum}}</text>
												<text><text class="cuIcon-commentfill"></text>{{item2.commentNum}}</text>
											</view>
										</view>
										<image
											v-if="hasCoverImage(item2.coverimage, item2.id)"
											class="publish-cover"
											:src="item2.coverimage"
											mode="aspectFill"
											@error="onCoverError(item2.id)"
										></image>
									</view>
									<view class="publish-delete" @click="del(item2.id,index,index2,item2.title)">
										<text class="cuIcon-delete"></text>
									</view>
								</view>
							</view>
						</view>
					</block>
					<view class="publish-empty" v-else>
						<text>还没有发布内容</text>
					</view>
					<view class="text-center text-gray padding-tb-sm" :class="showNoResult?'show':'hide'">我是有底线的...</view>
				</view>
			</view>
		
		</scroll-view>
		<colorbar footerTab="4" :messageCount='messageCount'></colorbar>
	</view>
</template>

<script>
	var _this;var page=1;
	import colorbar from "@/components/color-bar.vue"
	export default {
		 components: {colorbar },
		data() {
			return {
				checkbox: [{
					value: 0,
					name: '',
					checked: false,
					hot: false,
				}],
				modalName: null,
				gridCol: 3,
				gridBorder: false,
				menuBorder: true,
				menuArrow: true,
				menuCard: true,
				skin: true,
				listTouchStart: 0,
				listTouchDirection: null,
				selectList:[],
				labelList:[],
				user:'',//用户数据
				favorTotalCount:0,//点赞数据
				myUserData:{
					attentionLists:[],
					attentionListsCount:0,
					beAttentionLists:[],
					beAttentionListsCount:0,
					favorLists:[],
					favorListsCount:0,
					favorTotalCount:0
				},//具体列表项数据
				discoverLists:[],//动态列表数据
				total:'',
				showNoResult:false,
				totalPage:0,
				messageCount:0,
				failedCoverMap:{}
			};
		},
		mounted() {
			_this=this;
		},
		onLoad() {
			
		},
		onShow() {
			page=1;
			this.discoverLists=[];
			this.total='';
			for(var j=0; j<this.checkbox.length; j++){
				if(this.checkbox[j].checked){
					this.labelList.push(this.checkbox[j])
				}
			 }
			this.user = this.$common.userInfo();
			console.log('this.user',this.user)
			if (typeof(this.user)== "undefined" || this.user=='' || this.user==[] ||  this.user==null) {
				console.log('未登录状态');
				//this.$common.navigateTo('login');
			}else{
				this.$api.refreshUser(
				{},
				val => {
					if(val.code==1){
						this.user = val.data.user;
						this.refreshAppTheme(this.user);
						this.auth=val.data.auth;
						this.messageCount=val.data.msgCount;
						this.$db.set('user',val.data.user);
						this.$db.set('auth',val.data.auth);
						this.userDataListsDataLists();
						this.doMyDiscoverListsDataOp();
					}else{
						this.user=''
						this.$db.$db('user');
						this.$db.$db('auth');
					}
				})
			}
			
		},
		onReachBottom() {
			if(this.totalPage>=page){
				console.log("page: ",page);
				this.doMyDiscoverListsDataOp();
			}else{
				this.showNoResult=true
			}
		},
		methods: {
			loginGo(){
				this.$common.navigateTo('/pages/user/login');
			},
			userInfo(){
				this.$common.navigateTo('/pages/user/userInfo');
			},
			hasCoverImage(url,id){
				return !!url && String(url).trim() !== '' && String(url).indexOf('example.com') === -1 && !this.failedCoverMap[id];
			},
			onCoverError(id){
				this.$set(this.failedCoverMap, id, true);
			},
			//作品列表
			doMyDiscoverListsDataOp(){
					this.$api.doMyDiscoverListsData(
					{page:page,limit:10},
					data => {
						console.log(data);
						var res=data.data;
						if (data.code == 1) {
							// if(res.list.length<10){
							// 	_this.showNoResult=true;
							// }
							page++;
							_this.discoverLists=_this.discoverLists.concat(res.list);
							_this.total=res.count;
							_this.totalPage=res.total
							
						}
					}
					)  
			},
			logout(){
				_this.$common.modelShow('退出登录','确认退出登录吗?',()=>{
					_this.logoutOp();
				},function(){},true,'取消','确定')
			},
			logoutOp(){
				_this.$api.logout(
				{},
				data => {
					if (data.code == 1) {
						_this.$common.successToShow(data.msg,function(){
							_this.$db.del('upload', 1)
							_this.$db.del('login', 1)
							_this.$db.del('token')
							_this.$db.del('user')	
							_this.$db.del('auth')	
							_this.$common.navigateTo('../index/index');
						});
					}else{
						
					}
				}
				)  
			},
			//跳转详情
			detail(id){
				_this.$common.navigateTo('../index/detail?id='+id);
			},
			//删除动态
			del(id,index,index2,title){
				_this.$common.modelShow('删除确认','确认删除"'+title+'"的动态吗?',()=>{
					_this.delDiscoverOp(id,index,index2);
				},function(){},true,'取消','确定')
			},
			delDiscoverOp(id,index,index2){
				this.$api.delData(
					{discover_id:id},
					data => {
						var res=data.data;
						if (data.code == 1) {
							_this.discoverLists[index]['list'].splice(index2, 1);
							uni.showToast({
								title:data.msg,
								icon:'success'
							})
							//_this.$common.successToshow(data.msg);
						}else{
							uni.showToast({
								title:data.msg,
								icon:'none'
							})
						}
					}
				)  
			},
			userDataListsDataLists(){
				this.$api.userDataListsData(
					{},
					data => {
						console.log(data);
						var res=data.data;
						if (data.code == 1) {
							_this.myUserData=res;
						}
					}
				)  
			},
			userListClick(type){
				if(this.user){
					_this.$common.navigateTo('myattentions?type='+type);
				}else{
					uni.showModal({
						title: '登录',
						content: '该功能需要登录才能查看！',
						confirmText:'去登录',
						success: (res)=> {
							if (res.confirm) {
								uni.navigateTo({
									url:'/pages/user/login'
								})
							} else if (res.cancel) {
								console.log('用户点击取消');
							}
						}
					});
				}
				
			},
			ChooseCheckbox(e) {
				this.selectList=[];
				let items = this.checkbox;
				let values = e.currentTarget.dataset.value;
				for (let i = 0, lenI = items.length; i < lenI; ++i) {
					if (items[i].value == values) {
						items[i].checked = !items[i].checked;
						break
					}
				}
				for(var j=0; j<items.length; j++){
					if(items[j].checked){
						this.selectList.push(items[j])
					}
				}
			},
			//确定按钮
			confirmBtn(){
				this.labelList=[];
				this.labelList=this.selectList;
				this.hideModal();
			},
			showModal(e) {
				this.modalName = e.currentTarget.dataset.target
			},
			hideModal(e) {
				this.modalName = null
			},
			Gridchange(e) {
				this.gridCol = e.detail.value
			},
			Gridswitch(e) {
				this.gridBorder = e.detail.value
			},
			MenuBorder(e) {
				this.menuBorder = e.detail.value
			},
			MenuArrow(e) {
				this.menuArrow = e.detail.value
			},
			MenuCard(e) {
				this.menuCard = e.detail.value
			},
			SwitchSex(e) {
				this.skin = e.detail.value
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

<style lang="scss">
	.header{
		background: #fff;
		.img image{display: block;width: 100rpx;height: 100rpx;border-radius: 50%;}
		.right{
			width: 590rpx;
			.name{color: #333;font-size: 30rpx;}
			.icon image{display: block;width: 30rpx;height: 30rpx;opacity: 0.5;}
			.num{
				view{
					color: #333; font-size: 28rpx;width: 50%;
					text{color: #3396fb;}
				}
			}
		}
	}
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
	.publish-section{
		padding: 18rpx 24rpx 24rpx;
	}
	.publish-header{
		display:flex;
		align-items:center;
		justify-content:space-between;
		padding: 0 2rpx 12rpx;
	}
	.publish-title{
		display:flex;
		align-items:center;
		font-size: 30rpx;
		color:#2f3440;
		font-weight:600;
	}
	.publish-dot{
		width: 12rpx;
		height: 12rpx;
		border-radius:50%;
		background:#ff8a2a;
		margin-right:16rpx;
	}
	.publish-total{
		font-size: 28rpx;
		color:#98a0ad;
	}
	.publish-group{
		margin-bottom: 14rpx;
	}
	.publish-date{
		font-size: 24rpx;
		color:#9aa3b2;
		padding: 0 8rpx 10rpx;
	}
	.publish-card{
		background:#fff;
		border-radius:20rpx;
		padding: 0 20rpx;
	}
	.publish-item{
		display:flex;
		align-items:center;
		padding: 20rpx 0;
		border-bottom: 1rpx solid #f3f4f6;
	}
	.publish-item:last-child{
		border-bottom:none;
	}
	.publish-main{
		flex:1;
		display:flex;
		align-items:center;
		min-width:0;
	}
	.publish-content{
		flex:1;
		min-width:0;
	}
	.publish-text{
		font-size: 28rpx;
		line-height: 1.5;
		color:#2f3440;
		word-break:break-all;
	}
	.publish-meta{
		display:flex;
		align-items:center;
		flex-wrap:wrap;
		margin-top: 10rpx;
		font-size: 22rpx;
		color:#a4acb8;
	}
	.publish-meta text{
		margin-right: 16rpx;
	}
	.publish-meta .cuIcon-appreciatefill,
	.publish-meta .cuIcon-commentfill{
		margin-right: 6rpx;
		font-size: 20rpx;
	}
	.publish-cover{
		width: 96rpx;
		height: 96rpx;
		border-radius:16rpx;
		margin-left: 16rpx;
		flex-shrink:0;
		background:#f5f7fb;
	}
	.publish-delete{
		width: 44rpx;
		height: 44rpx;
		display:flex;
		align-items:center;
		justify-content:center;
		color:#c9cfd8;
		font-size: 26rpx;
		margin-left: 10rpx;
	}
	.publish-empty{
		background:#fff;
		border-radius:20rpx;
		padding: 44rpx 24rpx;
		text-align:center;
		color:#a1a9b5;
		font-size: 26rpx;
	}
</style>



