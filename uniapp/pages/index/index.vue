<template>
  <view class="content">
	  <cu-custom bgColor="bg-gradual-purple">
	  	<block slot="backText">返回</block>
	  	<block slot="content">瀑布流种草分享</block>
	  </cu-custom>
	  <view class="cu-bar bg-gradual-purple search">
	  	<view class="cu-avatar round" :style="{'background-image':'url('+baseLogo+')'}"  @tap="userCenter"></view>
	  	<view class="search-form round">
	  		<text class="cuIcon-search"></text>
	  		<input @focus="InputFocus" @blur="InputBlur" :value="keywords"  :adjust-position="false" type="text" placeholder="搜索图片、文章、视频" confirm-type="search"></input>
	  	</view>
	  	<view class="action" @tap="addressChange">
	  		<text>{{cityName}}</text>
	  		<text class="cuIcon-triangledownfill"></text>
	  	</view>
	  </view>
	  <!-- <text class="cuIcon-camerafill"></text>  -->
	  <scroll-view scroll-x class="bg-gradual-purple  nav">
	  	<view class="cu-item" :class="item.id==navId?'text-white cur':''" v-for="(item,index) in navData" :key="index" @tap="tabSelect" :data-id="item.id" :data-index="index">
	  		{{item.label}}
	  	</view>
	  </scroll-view>
	  <view class="home-banner-wrap" v-if="homeBanners.length > 0">
	  	<swiper class="home-banner-swiper" :indicator-dots="homeBanners.length > 1" indicator-color="rgba(255,255,255,0.5)" indicator-active-color="#ffffff" :autoplay="homeBanners.length > 1" :interval="4000" :duration="500" circular>
	  		<swiper-item v-for="(item,index) in homeBanners" :key="'banner-'+index">
	  			<view class="home-banner-item" @tap="handleBannerTap(item)">
	  				<image class="home-banner-image" :src="item.image" mode="aspectFill"></image>
	  				<view class="home-banner-mask" v-if="item.title">
	  					<text class="home-banner-title">{{ item.title }}</text>
	  				</view>
	  			</view>
	  		</swiper-item>
	  	</swiper>
	  </view>
		
		<!--uv-ui瀑布流组件-->
		<view class="contentWarp">
			<uv-waterfall ref="waterfall"
						v-model="list"
						:add-time="100"
						:left-gap="leftGap"
						:right-gap="rightGap"
						:column-gap="columnGap"
						@changeList="changeList">
						<!-- 第一列数据 -->
						<template v-slot:list1> 
							<!-- 为了磨平部分平台的BUG，必须套一层view -->
							<view>
								<view v-for="(item, index) of list1" :key="'t-'+index" class="oneshare">
									<view class="img_body" :data-index="item.index" :data-page="item.page" @click="imageClick">
										<view class="img_zzc" v-if="item.type=='video'"><text class="cuIcon-playfill"></text></view>
										<image
										  class="waterfalls-list-image"
										  mode="widthFix"
										  :src="item.image_url"
										></image>
									</view>
								  <view class="cnt" :data-id="item.id" @click="detailTab">
								    <view class="title">{{ item.title }}</view>
								    <view class="text">{{ item.text }}</view>
								  <view class="user flex_layout">
								  	<view  class="uinfo"><image v-if="item.avatar!=''" :src="item.avatar"></image><image v-else src="../../static/images/avatar.png"></image>{{item.nickname}}</view>
								  	<view>
								  				<view class="text-gray text-sm">
								  					<!-- <text class="cuIcon-attentionfill margin-lr-xs"></text> 10 -->
								  					<text class="cuIcon-appreciatefill margin-lr-xs"></text>{{item.favorNum}}
								  					<text class="cuIcon-messagefill margin-lr-xs"></text> {{item.commentNum}}
								  				</view>
								  			</view>
								  </view>
								  </view>
								</view>
							</view>
						</template>
						<!-- 第二列数据 -->
						<template v-slot:list2>
							<!-- 为了磨平部分平台的BUG，必须套一层view -->
							<view>
								<view v-for="(item, index) of list2" :key="'t-'+index"  class="oneshare">
									<view class="img_body" :data-index="item.index" :data-page="item.page" @click="imageClick">
										<view class="img_zzc" v-if="item.type=='video'"><text class="cuIcon-playfill"></text></view>
										<image
										  class="waterfalls-list-image"
										  mode="widthFix"
										  :src="item.image_url"
										></image>
									</view>
								  <view class="cnt" :data-id="item.id" @click="detailTab">
								    <view class="title">{{ item.title }}</view>
								    <view class="text">{{ item.text }}</view>
								  <view class="user flex_layout">
								  	<view class="uinfo"><image v-if="item.avatar!=''" :src="item.avatar"></image><image v-else src="../../static/images/avatar.png"></image>{{item.nickname}}</view>
								  	<view>
								  				<view class="text-gray text-sm">
								  					<!-- <text class="cuIcon-attentionfill margin-lr-xs"></text> 10 -->
								  					<text class="cuIcon-appreciatefill margin-lr-xs"></text>{{item.favorNum}}
								  					<text class="cuIcon-messagefill margin-lr-xs"></text> {{item.commentNum}}
								  				</view>
								  			</view>
								  </view>
								  </view>
								</view>
							</view>
						</template>
					</uv-waterfall>
		</view>
	<view class="text-center text-gray padding" v-if="list.length==0">暂无更多数据...</view>
	<view class="text-center text-gray padding-tb-sm" :class="showNoResult?'show':'hide'" v-if="list.length>0">我是有底线的...</view>
	
	<view class="cu-modal" :class="groupQrcodeVisible ? 'show' : ''" @tap="hideGroupQrcode">
		<view class="cu-dialog group-qrcode-dialog" @tap.stop="">
			<view class="cu-bar bg-white justify-end">
				<view class="content">{{ groupQrcode ? groupQrcode.title : '进群提醒' }}</view>
				<view class="action" @tap="hideGroupQrcode"><text class="cuIcon-close text-gray"></text></view>
			</view>
			<view class="padding-lr padding-bottom bg-white" v-if="groupQrcode">
				<view class="text-gray text-sm margin-bottom-sm" v-if="groupQrcode.description">{{ groupQrcode.description }}</view>
				<image class="group-qrcode-image" :src="groupQrcode.image" mode="aspectFit" @tap="previewGroupQrcode"></image>
				<view class="text-gray text-sm margin-top-sm" v-if="groupQrcode.school_name">{{ groupQrcode.school_name }}</view>
				<view class="flex justify-center margin-top">
					<button class="cu-btn line-purple round margin-right-sm" @tap="hideGroupQrcode">稍后再看</button>
					<button class="cu-btn bg-purple round" @tap="previewGroupQrcode">查看二维码</button>
				</view>
			</view>
		</view>
	</view>
	<colorbar footerTab="0" :messageCount='messageCount'></colorbar>
  </view>
</template>

<script>
var page=1,limit=10; var _this;
import {baseLogo} from '../../config/config.js';
import colorbar from "@/components/color-bar.vue";
export default {
  components: { colorbar },
  data() {
    return {
	  baseLogo:baseLogo,
	  TabCur: 0,
	  scrollLeft: 0,
	  type:0,
	  cityName:'请选择学校',
	  messageCount:0,
	  navData:[],
	  navId:0,
	  totalPage:'',
	  showNoResult:false,
	  latlng:'',
	  keywords:'',
	  user:[],
	  user_id:'',
		list: [],// 瀑布流全部数据
		list1: [],// 瀑布流第一列数据
		list2: [],// 瀑布流第二列数据
		leftGap: 5,
		rightGap: 5,
		columnGap: 5,
		per_page:0,
		last_page:0,
		current_page:0,
        groupQrcodeVisible:false,
        groupQrcode:null,
        homeBanners:[],
    };
  },
  onLoad(e) {
	
  },
  onShow() {
     _this=this;
     _this.list=[];
     _this.list1=[];
     _this.list2=[];
     this.user = this.$common.userInfo();
     if(this.user && this.user.id){
         this.$api.refreshUser({}, val => {
             if(val.code==1){
                 this.user = val.data.user;
                 this.user_id = this.user.id;
                 this.baseLogo = this.user.avatar || this.baseLogo;
                 this.messageCount = val.data.msgCount;
                 if(this.user.school_id){
                     this.cityName = this.user.school_name || '已绑定学校';
                 }else{
                     this.cityName = '请选择学校';
                     this.navData = [];
                     this.homeBanners = [];
                     this.groupQrcodeVisible = false;
                     this.groupQrcode = null;
                     uni.navigateTo({ url: '/pages/plugin/gate' });
                     return;
                 }
                 this.typeDataLists();
                 this.loadGroupQrcode(this.user.school_id || 0);
                 this.loadHomeBanners(this.user.school_id || 0);
             }
         })
     }else{
         this.cityName='请选择学校';
         this.navData=[];
         this.homeBanners=[];
         this.groupQrcodeVisible=false;
         this.groupQrcode=null;
         uni.navigateTo({ url: '/pages/plugin/gate' });
         return;
     }
  },
  mounted() {
      _this = this;
  },
  onReachBottom() {
   if(this.totalPage>=page){
   	this.getListHandle();
   }else{
   	this.showNoResult=true
   }
  },
  onPullDownRefresh() {
  	page = 1;
  	this.list = [];
  	this.list1 = [];
  	this.list2 = [];
  	this.totalPage = '';
  	this.showNoResult = false;
  	if (this.$refs.waterfall) {
  		this.$refs.waterfall.clear();
  	}
  	this.getListHandle();
  	uni.stopPullDownRefresh();
  },
  methods:{
        // 这点非常重要：e.name在这里返回是list1或list2，要手动将数据追加到相应列
        changeList(e){
            this[e.name].push(e.value);
        },
        isGroupQrcodeExpired(config){
            if(!config){
                return true;
            }
			const now = Math.floor(Date.now() / 1000);
			if(config.starttime && Number(config.starttime) > 0 && Number(config.starttime) > now){
				return true;
			}
			if(config.endtime && Number(config.endtime) > 0 && Number(config.endtime) < now){
				return true;
			}
			return false;
		},
		shouldShowGroupQrcode(config){
			if(!config || this.isGroupQrcodeExpired(config)){
				return false;
			}
			const key = 'groupQrcodePopup:' + config.id;
			const lastShowAt = Number(uni.getStorageSync(key) || 0);
			const now = Math.floor(Date.now() / 1000);
			if(config.popup_strategy === 'always'){
				return true;
			}
			if(config.popup_strategy === 'daily'){
				const todayStart = new Date();
				todayStart.setHours(0, 0, 0, 0);
				return lastShowAt < Math.floor(todayStart.getTime() / 1000);
			}
			const intervalDays = Math.max(1, Number(config.popup_interval || 1));
			return !lastShowAt || (now - lastShowAt) >= intervalDays * 86400;
		},
		markGroupQrcodeShown(config){
			if(!config || !config.id){
				return;
			}
			uni.setStorageSync('groupQrcodePopup:' + config.id, Math.floor(Date.now() / 1000));
		},
		loadGroupQrcode(schoolId){
			this.$api.groupQrcodeData({school_id: schoolId || 0}, res => {
				if(res.code === 1 && res.data && res.data.config){
					const config = res.data.config;
					if(this.shouldShowGroupQrcode(config)){
						this.groupQrcode = config;
						this.groupQrcodeVisible = true;
						this.markGroupQrcodeShown(config);
					}
				}
			});
		},
		hideGroupQrcode(){
			this.groupQrcodeVisible = false;
		},
		previewGroupQrcode(){
			if(!this.groupQrcode || !this.groupQrcode.image){
				return;
			}
			uni.previewImage({
				urls:[this.groupQrcode.image],
				current:this.groupQrcode.image
			});
		},
		isSafeBannerPath(path){
			return typeof path === 'string' && /^\/pages\/[A-Za-z0-9_\/-]+(\?.*)?$/.test(path);
		},
		loadHomeBanners(schoolId){
			this.$api.homeBannerData({school_id: schoolId || 0, limit: 5}, res => {
				if(res.code === 1 && res.data){
					this.homeBanners = Array.isArray(res.data.list) ? res.data.list : [];
				}
			});
		},
		reportBannerClick(id){
			if(!id){
				return;
			}
			this.$api.homeBannerClickData({id:id}, () => {});
		},
		handleBannerTap(item){
			if(!item || !item.id){
				return;
			}
			this.reportBannerClick(item.id);
			if(item.jump_type === 'discover'){
				const discoverId = Number(item.jump_value || 0);
				if(discoverId > 0){
					this.$common.navigateTo('detail?id=' + discoverId);
				}
				return;
			}
			if(item.jump_type === 'path' && this.isSafeBannerPath(item.jump_value)){
				this.$common.navigateTo(item.jump_value);
			}
		},
	  //判断是否授权打开定位
	  isLocation:function(){
	  	uni.authorize({
	  	    scope: 'scope.userLocation',
	  	    success(res) {
	  	        uni.getLocation();
				return true
	  	    },
			error:function(res){
				return false;
				console.log("res: ",res);
			}
	  	})
	  },
	  getLocation:function(){
	  	var that=this;
	  	uni.getLocation({
	  	    type: 'gcj02',
	  	    success: function (res) { console.log("res: ",res);
	  			that.latlng = res.latitude+','+res.longitude;
	  			console.log("res: ",res);
	  	        console.log('当前位置的经度：' + res.longitude);
	  	        console.log('当前位置的纬度：' + res.latitude);
	  			that.nowLocation(that.latlng);
	  	    },
	  		error:function(res){
				that.typeDataLists();	
	  			that.isLocation();
	  			console.log("res: ",res);
	  		}
	  	});
	  },
	  nowLocation(latlng){
	  	var that=this;
	  			this.$api.locationAddress(
	  			{latlng:latlng},
	  			data => {
	  				var res=data.data;
	  				if (data.code == 1) {
	  					if(!(this.user && this.user.school_id)){
	  						that.cityName=res.city;
	  					}
	  					that.typeDataLists();
	  				}
	  			}
	  			)  
	  },
	  //分类数据
	  typeDataLists(){
	  		_this.$api.typeData(
	  		{type:0},
	  		data => {
	  			console.log(data);
	  			var res=data.data;
	  			if (data.code == 1) {
	  				_this.navData=res;
	  				if(res.length>0){
	  					_this.navId=res[0]['id'];
						page=1;
						_this.getListHandle();
	  				}
	  			}
	  		}
	  		)  
	  },
	  getListHandle() {
		this.user = this.$common.userInfo();
		if(this.user){
			this.user_id=this.user.id;
			this.baseLogo=this.user.avatar;
		}
		var cityNm=this.cityName;
		var schoolId = this.user && this.user.school_id ? this.user.school_id : 0;
		if(schoolId>0){
			cityNm='';
		}else if(this.cityName=='全国' || this.cityName=='请选择学校'){
			cityNm='';
		}
	  	this.$api.indexData(
	  		{type:this.navId,keywords:this.keywords,location:cityNm,school_id:schoolId,page:page,limit:limit,user_id:this.user_id},
	  		data => {
	  			var res=data.data.data;
					this.per_page=res.per_page;
					this.current_page=res.current_page;
					let arr=res.data.map(item=>{
						return{
							...item,
							page:res.current_page
						}
					})
	  			if (data.code == 1) {
	  				if(res.current_page>=res.last_page){
	  					_this.showNoResult=true;
	  				}
	  				page++;
	  				_this.list=_this.list.concat(arr);
					_this.totalPage=res.last_page;
					_this.messageCount=data.data.msgCount;
	  			}else{
					_this.$common.normalToShow(data.msg)
				}
	  		}
	  		)  
	  },
	  tabSelect(e){
	  	 _this.navIndex=e.currentTarget.dataset.index;
		   _this.navId=e.currentTarget.dataset.id;
		   page=1;
		   this.list = [];
			 this.$refs.waterfall.clear();
			 this.list1 = [];
			 this.list2 = [];
		   _this.totalPage='';
		   _this.getListHandle();
	  },
	  imageClick(e){
			var index=e.currentTarget.dataset.index;
			var bfPage=e.currentTarget.dataset.page;
			let current=(bfPage-1)*limit+index
			const imgs=this.list.map(item=>{
				return item.image_url
			})
			uni.previewImage({
				urls:imgs,
				current:current
			})
	  },
		detailTab(e){
			var id=e.currentTarget.dataset.id;
			this.$common.navigateTo('detail?id='+id);
		},
	  userCenter(){
	  	  uni.navigateTo({
	  	  	url:'/pages/user/index'
	  	  })
	    },
	  addressChange(){
	  	this.user = this.$common.userInfo();
	  	if(!this.user || !this.user.id){
	  		this.$common.navigateTo('/pages/user/login');
	  		return;
	  	}
	  	if(this.user.school_id && String(this.user.school_locked)==='1'){
	  		this.$common.normalToShow('学校已绑定，如需修改请联系管理员');
	  		return;
	  	}
	  	this.$common.navigateTo('/pages/plugin/index')
	  },
	  InputFocus(e){
		  this.keywords=e.detail.value;
	  },
	  InputBlur(e){
	  	  this.keywords=e.detail.value;
		  page=1;
		  _this.list=[];
		  _this.list1=[];
		  _this.list2=[];
		  _this.totalPage='';
		  _this.showNoResult=false;
		  if(this.$refs.waterfall){
		  	this.$refs.waterfall.clear();
		  }
		  _this.getListHandle();
	  },
  }
};
</script>
<style lang="scss">
	page {
	  background-color: #fff;
	}
	.show{ display: block;}
	.hide{ display: none;}
	.img_body{
		position: relative;
		.img_zzc{
			position: absolute;width: 45rpx;height: 45rpx;left: 20rpx;top: 20rpx;border-radius: 50%;background: rgba(0, 0, 0, 0.5);z-index: 99;display: flex;align-items: center;justify-content: center;
			text{color: #fff;font-size:28rpx;z-index: 100;}
		}
		
		.waterfalls-list-image {
		  width: 100%;
		  will-change: transform;
		  border-radius: 10px 10px 0 0;
		  display: block;
		}
	}
	
.contentWarp{ padding: 10px 0; background-color: #f7f7f7;}
.flex_layout{
	display: flex;
	justify-content: space-between;
	align-items: center;
}
.user{ padding: 20rpx 0 0;
	 font-size: 22rpx;
	 height: 80rpx; 
	 line-height: 50rpx;
     image{
		 height: 30rpx; 
		 width: 30rpx;
		 border-radius: 50%;
		 vertical-align: middle;
		 display: inline-block;
		 margin-right: 10rpx;
		 border: 1px solid #f7f7f7;
	 }
	 }
.group-qrcode-dialog{
	width: 620rpx;
}
.group-qrcode-image{
	width: 420rpx;
	height: 420rpx;
	border-radius: 16rpx;
	background: #f7f7f7;
}
.home-banner-wrap{
	padding: 20rpx 20rpx 0;
	background: #fff;
}
.home-banner-swiper{
	height: 280rpx;
}
.home-banner-item{
	position: relative;
	height: 280rpx;
	border-radius: 20rpx;
	overflow: hidden;
}
.home-banner-image{
	width: 100%;
	height: 100%;
	display: block;
	background: #f7f7f7;
}
.home-banner-mask{
	position: absolute;
	left: 0;
	right: 0;
	bottom: 0;
	padding: 20rpx 24rpx;
	background: linear-gradient(180deg, rgba(0,0,0,0) 0%, rgba(0,0,0,0.55) 100%);
}
.home-banner-title{
	color: #fff;
	font-size: 28rpx;
	line-height: 1.4;
}
.header_search{
	padding: 20rpx;
	.header_search_flex{
		background: #fff; 
	    height: 60rpx; 
		line-height: 60rpx;
		width: 100%;
		border-radius: 60rpx; 
		border:1px solid #e7e7e7;
		background-color: #f7f7f7;
		.searchBox{
			width: 100%;
			overflow: hidden;
			input{
				height: 60rpx; 
				line-height: 60rpx;
				text-indent: 20rpx;
				font-size: 24rpx;
				width:100%
			}
		}
		.searchBtn{
			width: 55rpx;
			height: 55rpx;
			background-color: #f7f7f7;
			border-radius: 55rpx; 
			margin-right: 5rpx;
			image{
				height: 30rpx;
				height: 30rpx;
			}
		}
		
		}
	.flex_layout{
		display: flex;
		justify-content: space-between;
	}
	}
	
.content {
  padding:0;
  .oneshare{margin-bottom:10rpx; background: #fff; border-radius:10rpx; overflow: hidden;}
  .cnt {
    padding: 10rpx 10rpx 10rpx 10rpx;
    .title {
      font-size: 32rpx;
	  word-break: break-all;
	  color: #474747;
    }
    .text {
      font-size: 28rpx;
      margin-top: 10rpx;
	   word-break: break-all;
	   color: #999999;
    }
  }
}
.uinfo{ font-size:20rpx;}
</style>



