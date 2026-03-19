<template>
	<view class="user-page-root" :style="themeVarsStyle">
		<view class="user-topbar" :style="userTopbarStyle">
			<view class="user-topbar__main">
				<view class="user-topbar__name">我的</view>
			</view>
		</view>
		<scroll-view :scroll-y="modalName==null" class="page" :class="modalName!=null?'show':''" :style="userPageStyle">
			<view class="profile-section" v-if="user">
				<view class="profile-card" @click="userInfo">
					<view class="profile-card__top">
						<view class="profile-avatar" :style="{'background-image':'url('+user.avatar+')'}"></view>
						<view class="profile-main">
							<view class="profile-name-row">
								<view class="profile-name text-cut">{{user.nickname}}</view>
								<view class="profile-logout" @click.stop="logout">
									<text>退出</text>
									<text class="cuIcon-right"></text>
								</view>
							</view>
							<view class="profile-bio text-cut">{{user.bio || '还没有填写个人介绍'}}</view>
						</view>
					</view>
				</view>
			</view>
			<view class="profile-login-card" @click="loginGo" v-else>
				<view class="profile-login-card__avatar"><image src="@/static/images/avatar.png" mode="aspectFill"></image></view>
				<view class="profile-login-card__main">
					<view class="profile-login-card__name">请先登录</view>
					<view class="profile-login-card__desc">登录后查看关注、点赞和发布记录</view>
				</view>
				<view class="profile-login-card__arrow"><text class="cuIcon-right"></text></view>
			</view>
	
			<view class="social-section" v-if="user">
				<view class="social-card">
					<view class="social-row" @click="userListClick(1)">
						<view class="social-row__left">
							<view class="social-row__icon is-purple"><text class="cuIcon-attentionfavorfill"></text></view>
							<view class="social-row__title">关注我的</view>
						</view>
						<view class="social-row__right">
							<view class="social-avatar-group" v-if="myUserData.beAttentionLists && myUserData.beAttentionLists.length">
								<view class="social-avatar" v-if="item.avatar!=''" :style="{'background-image':'url('+item.avatar+')'}" v-for="(item,index) in myUserData.beAttentionLists" :key="'be-'+index"></view>
								<view class="social-avatar" v-else style="background-image: url(../../static/images/avatar.png);" v-for="(item,index) in myUserData.beAttentionLists" :key="'be-empty-'+index"></view>
							</view>
							<text class="social-row__count">{{myUserData.beAttentionListsCount}} 人</text>
							<text class="cuIcon-right social-row__arrow"></text>
						</view>
					</view>
					<view class="social-row" @click="userListClick(2)">
						<view class="social-row__left">
							<view class="social-row__icon is-orange"><text class="cuIcon-friendaddfill"></text></view>
							<view class="social-row__title">我关注的</view>
						</view>
						<view class="social-row__right">
							<view class="social-avatar-group" v-if="myUserData.attentionLists && myUserData.attentionLists.length">
								<view class="social-avatar" v-if="item.avatar!=''" :style="{'background-image':'url('+item.avatar+')'}" v-for="(item,index) in myUserData.attentionLists" :key="'att-'+index"></view>
								<view class="social-avatar" v-else style="background-image: url(../../static/images/avatar.png);" v-for="(item,index) in myUserData.attentionLists" :key="'att-empty-'+index"></view>
							</view>
							<text class="social-row__count">{{myUserData.attentionListsCount}} 人</text>
							<text class="cuIcon-right social-row__arrow"></text>
						</view>
					</view>
					<view class="social-row is-last" @click="userListClick(3)">
						<view class="social-row__left">
							<view class="social-row__icon is-pink"><text class="cuIcon-emojiflashfill"></text></view>
							<view class="social-row__title">点赞我的</view>
						</view>
						<view class="social-row__right">
							<view class="social-avatar-group" v-if="myUserData.favorLists && myUserData.favorLists.length">
								<view class="social-avatar" v-if="item.avatar!=''" :style="{'background-image':'url('+item.avatar+')'}" v-for="(item,index) in myUserData.favorLists" :key="'favor-'+index"></view>
								<view class="social-avatar" v-else style="background-image: url(../../static/images/avatar.png);" v-for="(item,index) in myUserData.favorLists" :key="'favor-empty-'+index"></view>
							</view>
							<text class="social-row__count">{{myUserData.favorListsCount}} 次</text>
							<text class="cuIcon-right social-row__arrow"></text>
						</view>
					</view>
				</view>
			</view>

			<view class="user-hidden-tools">

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
				
			</view>

			<view class="publish-section" v-if="user">
					<view class="publish-header">
						<view class="publish-title">我发布的</view>
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
				failedCoverMap:{},
				statusBarHeight:0,
				topbarHeight:50,
				topbarBottomGap:0
			};
		},
		computed:{
			userTopbarStyle(){
				return {
					paddingTop: this.statusBarHeight + 'px',
					height: (this.statusBarHeight + this.topbarHeight) + 'px'
				};
			},
			userPageStyle(){
				return {
					paddingTop: (this.statusBarHeight + this.topbarHeight + this.topbarBottomGap + 6) + 'px',
					boxSizing: 'border-box'
				};
			}
		},
		mounted() {
			_this=this;
		},
		onLoad() {
			this.initTopbarMetrics();
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
			initTopbarMetrics(){
				let statusBarHeight=Number(this.StatusBar || 0);
				let systemInfo={};
				try{
					systemInfo=uni.getSystemInfoSync() || {};
				}catch(error){
					systemInfo={};
				}
				if(!statusBarHeight){
					statusBarHeight=Number(systemInfo.statusBarHeight || 0);
				}
				this.statusBarHeight=statusBarHeight;
			},
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
	.user-page-root{
		min-height: 100vh;
		background: #f7f8fb;
	}
	.user-topbar{
		position: fixed;
		top: 0;
		left: 0;
		right: 0;
		z-index: 30;
		display:flex;
		align-items:center;
		padding: 0 24rpx;
		box-sizing: border-box;
		background:#ffffff;
		border-bottom: 1rpx solid rgba(226, 232, 240, 0.72);
	}
	.user-topbar__main{
		flex:1;
		min-width:0;
		height: 100%;
		display:flex;
		align-items:center;
	}
	.user-topbar__name{
		font-size: 30rpx;
		line-height: 1;
		color:#111827;
		font-weight: 400;
		letter-spacing: .6rpx;
		padding-left: 14rpx;
	}
	.user-hidden-tools{
		position: absolute;
		width: 0;
		height: 0;
		overflow: hidden;
	}
	.profile-section{
		padding: 20rpx 24rpx 0;
	}
	.profile-card{
		position: relative;
		overflow: hidden;
		padding: 26rpx;
		border-radius: 28rpx;
		background: #ffffff;
		border: 1rpx solid #edf2f7;
		box-shadow: 0 10rpx 26rpx rgba(148, 163, 184, 0.12);
	}
	.profile-card::after{
		content: '';
		position: absolute;
		right: -20rpx;
		top: -24rpx;
		width: 140rpx;
		height: 140rpx;
		border-radius: 50%;
		background: linear-gradient(135deg, rgba(141,169,255,0.12) 0%, rgba(141,169,255,0) 100%);
	}
	.profile-card__top{
		position: relative;
		z-index: 1;
		display: flex;
		align-items: center;
	}
	.profile-avatar{
		width: 104rpx;
		height: 104rpx;
		border-radius: 30rpx;
		background-color: #eef2ff;
		background-position: center;
		background-size: cover;
		background-repeat: no-repeat;
		border: 4rpx solid #ffffff;
		box-shadow: 0 10rpx 22rpx rgba(148, 163, 184, 0.18);
		flex-shrink: 0;
	}
	.profile-main{
		flex: 1;
		min-width: 0;
		padding-left: 20rpx;
	}
	.profile-name-row{
		display:flex;
		align-items:center;
		justify-content:space-between;
		gap: 14rpx;
	}
	.profile-name{
		flex:1;
		min-width:0;
		font-size: 36rpx;
		line-height: 1.2;
		font-weight: 600;
		color: #1f2937;
	}
	.profile-logout{
		display:inline-flex;
		align-items:center;
		justify-content:center;
		height: 44rpx;
		padding: 0 16rpx;
		border-radius: 999rpx;
		background: #f8fafc;
		border: 1rpx solid #e5e7eb;
		font-size: 22rpx;
		color: #64748b;
		flex-shrink: 0;
	}
	.profile-logout .cuIcon-right{
		margin-left: 6rpx;
		font-size: 20rpx;
	}
	.profile-bio{
		margin-top: 10rpx;
		font-size: 22rpx;
		line-height: 1.4;
		color: #94a3b8;
	}
	.profile-login-card{
		margin: 20rpx 24rpx 0;
		padding: 26rpx 24rpx;
		border-radius: 26rpx;
		background:#ffffff;
		display:flex;
		align-items:center;
		box-shadow: 0 10rpx 28rpx rgba(148, 163, 184, 0.12);
	}
	.profile-login-card__avatar image{
		display:block;
		width: 92rpx;
		height: 92rpx;
		border-radius: 28rpx;
		background:#eef2f7;
	}
	.profile-login-card__main{
		flex:1;
		min-width:0;
		padding: 0 18rpx;
	}
	.profile-login-card__name{
		font-size: 30rpx;
		font-weight: 600;
		color:#1f2937;
	}
	.profile-login-card__desc{
		margin-top: 8rpx;
		font-size: 22rpx;
		color:#94a3b8;
	}
	.profile-login-card__arrow{
		color:#c0c8d4;
		font-size: 24rpx;
	}
	.social-section{
		padding: 18rpx 24rpx 4rpx;
	}
	.social-card{
		background:#ffffff;
		border-radius: 26rpx;
		padding: 0 24rpx;
		box-shadow: 0 8rpx 22rpx rgba(148, 163, 184, 0.08);
	}
	.social-row{
		display:flex;
		align-items:center;
		justify-content:space-between;
		padding: 24rpx 0;
		border-bottom: 1rpx solid #eef2f7;
	}
	.social-row.is-last{
		border-bottom:none;
	}
	.social-row__left{
		display:flex;
		align-items:center;
		min-width:0;
	}
	.social-row__icon{
		width: 44rpx;
		height: 44rpx;
		border-radius: 14rpx;
		display:flex;
		align-items:center;
		justify-content:center;
		font-size: 22rpx;
		margin-right: 16rpx;
	}
	.social-row__icon.is-purple{
		background: rgba(141,169,255,0.16);
		color:#7f9ef6;
	}
	.social-row__icon.is-orange{
		background: rgba(255,161,96,0.16);
		color:#ff8d4d;
	}
	.social-row__icon.is-pink{
		background: rgba(244,114,182,0.14);
		color:#ec4899;
	}
	.social-row__title{
		font-size: 30rpx;
		line-height: 1.2;
		color:#334155;
		font-weight: 500;
	}
	.social-row__right{
		display:flex;
		align-items:center;
		justify-content:flex-end;
		min-width: 0;
		margin-left: 18rpx;
	}
	.social-avatar-group{
		display:flex;
		align-items:center;
		margin-right: 12rpx;
	}
	.social-avatar{
		width: 36rpx;
		height: 36rpx;
		border-radius: 50%;
		background-position:center;
		background-size:cover;
		background-repeat:no-repeat;
		border: 2rpx solid #ffffff;
		margin-left: -10rpx;
		background-color:#eef2f7;
	}
	.social-avatar:first-child{
		margin-left: 0;
	}
	.social-row__count{
		font-size: 24rpx;
		color:#94a3b8;
		white-space: nowrap;
	}
	.social-row__arrow{
		margin-left: 12rpx;
		font-size: 22rpx;
		color:#c0c8d4;
	}
	.page {
		height: 100Vh;
		width: 100vw;
		background: #f5f7fb;
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
		padding: 18rpx 24rpx 30rpx;
	}
	.publish-header{
		display:flex;
		align-items:center;
		justify-content:flex-start;
		padding: 4rpx 2rpx 16rpx;
	}
	.publish-title{
		display:flex;
		align-items:center;
		font-size: 32rpx;
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
		min-width: 48rpx;
		height: 48rpx;
		padding: 0 16rpx;
		border-radius: 999rpx;
		background:#ffffff;
		display:flex;
		align-items:center;
		justify-content:center;
		font-size: 24rpx;
		color:#7b8ba4;
		box-shadow: 0 8rpx 22rpx rgba(148, 163, 184, 0.12);
	}
	.publish-group{
		margin-bottom: 20rpx;
	}
	.publish-date{
		font-size: 24rpx;
		color:#9aa3b2;
		padding: 0 8rpx 12rpx;
	}
	.publish-card{
		background:#fff;
		border-radius:24rpx;
		padding: 0 24rpx;
		box-shadow: 0 8rpx 22rpx rgba(148, 163, 184, 0.08);
	}
	.publish-item{
		display:flex;
		align-items:flex-start;
		padding: 24rpx 0;
		border-bottom: 1rpx solid #f3f4f6;
	}
	.publish-item:last-child{
		border-bottom:none;
	}
	.publish-main{
		flex:1;
		display:flex;
		align-items:flex-start;
		min-width:0;
	}
	.publish-content{
		flex:1;
		min-width:0;
	}
	.publish-text{
		font-size: 30rpx;
		line-height: 1.45;
		color:#2f3440;
		font-weight: 500;
		word-break:break-all;
	}
	.publish-meta{
		display:flex;
		align-items:center;
		flex-wrap:wrap;
		margin-top: 12rpx;
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
		width: 104rpx;
		height: 104rpx;
		border-radius:18rpx;
		margin-left: 18rpx;
		flex-shrink:0;
		background:#f5f7fb;
	}
	.publish-delete{
		width: 52rpx;
		height: 52rpx;
		display:flex;
		align-items:center;
		justify-content:center;
		color:#c9cfd8;
		font-size: 26rpx;
		margin-left: 12rpx;
		margin-top: 24rpx;
		border-radius: 16rpx;
		background:#f8fafc;
	}
	.publish-empty{
		background:#fff;
		border-radius:24rpx;
		padding: 44rpx 24rpx;
		text-align:center;
		color:#a1a9b5;
		font-size: 26rpx;
		box-shadow: 0 12rpx 30rpx rgba(148, 163, 184, 0.08);
	}
</style>



