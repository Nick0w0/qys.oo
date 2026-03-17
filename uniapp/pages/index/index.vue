<template>
  <view class="content" :style="themeVarsStyle">
	  <view class="home-topbar" :style="schoolHeroStyle">
		<view class="home-topbar-row" :style="homeTopbarRowStyle">
			<view class="home-school-hero" @tap="addressChange">
				<view class="home-school-logo-shell">
					<image v-if="schoolLogoUrl" class="home-school-logo" :src="schoolLogoUrl" mode="aspectFill"></image>
					<view v-else class="home-school-logo-fallback">{{ schoolDisplayInitial }}</view>
				</view>
				<view class="home-school-main">
					<text class="home-school-name">{{ schoolDisplayName }}</text>
				</view>
			</view>
		</view>
	</view>
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
	<view class="home-top-nav-sticky" :style="homeTopNavStickyStyle">
		<scroll-view scroll-x class="nav home-top-nav">
			<view class="cu-item" :class="item.id==navId?'cur':''" v-for="(item,index) in navData" :key="index" @tap="tabSelect" :data-id="item.id" :data-index="index">
				{{item.label}}
			</view>
		</scroll-view>
	</view>

		<!-- 单列帖子流 -->
		<view class="contentWarp">
			<view class="home-feed-list" v-if="list.length > 0">
				<view class="feed-card" v-for="(item, index) in list" :key="item.feed_key || index">
					<view class="feed-card-head">
						<view class="feed-user" :data-id="item.id" @tap="detailTab">
							<image class="feed-user-avatar" v-if="item.avatar!=''" :src="item.avatar" mode="aspectFill"></image>
							<image class="feed-user-avatar" v-else src="../../static/images/avatar.png" mode="aspectFill"></image>
							<view class="feed-user-main">
								<view class="feed-user-name">{{item.feed_nickname || item.nickname}}</view>
							</view>
						</view>
						<text class="cuIcon-moreandroid feed-more"></text>
					</view>
					<view class="feed-card-body" :data-id="item.id" @tap="detailTab">
						<view class="feed-title" v-if="item.title">{{ item.title }}</view>
						<view class="feed-text" v-if="item.feed_text">{{ item.feed_text }}</view>
						<view class="feed-media-grid" v-if="item.feed_media && item.feed_media.length > 0">
							<view class="feed-media-item" v-for="(img, mediaIndex) in item.feed_media" :key="item.feed_key + '-img-' + mediaIndex" @tap.stop="previewFeedImages(item, mediaIndex)">
								<view class="feed-video-mask" v-if="item.type=='video' && mediaIndex===0"><text class="cuIcon-playfill"></text></view>
								<image class="feed-media-image" :src="img" mode="aspectFill"></image>
							</view>
						</view>
					</view>
					<view class="feed-card-foot">
						<view class="feed-card-meta">{{ item.feed_time_text }}</view>
						<view class="feed-card-actions">
							<view class="feed-card-action">
								<text class="cuIcon-appreciatefill"></text>
								<text>{{item.favorNum}}</text>
							</view>
							<view class="feed-card-action">
								<text class="cuIcon-messagefill"></text>
								<text>{{item.commentNum}}</text>
							</view>
						</view>
					</view>
				</view>
			</view>
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
        topbarCapsuleReserve:0,
        topbarNavStickyTop:88,
    };
  },
  onLoad(e) {
     this.initTopbarCapsuleReserve();
  },
  onReady() {
     this.syncTopbarStickyTop();
  },
  onShow() {
     _this=this;
     const cachedUser = this.$common.userInfo();
     this.user = cachedUser;
     if(cachedUser && cachedUser.id){
         if(!this.applyHomeUserState(cachedUser)){
             uni.navigateTo({ url: '/pages/plugin/gate' });
             return;
         }
         this.$api.refreshUser({}, val => {
             if(val && val.code==1 && val.data && val.data.user){
                try {
                    this.$db.set('auth', val.data.auth || {});
                    this.$db.set('user', val.data.user || {});
                } catch (error) {}
                 this.messageCount = Number(val.data.msgCount || 0);
                 if(!this.applyHomeUserState(val.data.user)){
                     uni.navigateTo({ url: '/pages/plugin/gate' });
                 }
                 return;
             }
             this.applyHomeUserState(cachedUser);
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
  	this.getListHandle();
  	uni.stopPullDownRefresh();
  },
  computed:{
        schoolInfo(){
            return this.user && this.user.school_info ? this.user.school_info : {};
        },
                schoolTheme(){
            return {
                logo: this.schoolInfo.logo || this.appTheme.logo || '',
                headerBgImage: this.schoolInfo.header_bg_image || this.appTheme.headerBgImage || '',
                primary: this.schoolInfo.theme_primary || this.appTheme.primary || '#8FBFF6',
                secondary: this.schoolInfo.theme_secondary || this.appTheme.secondary || '#C9E0FF',
                textColor: this.schoolInfo.theme_text_color || this.appTheme.textColor || '#111827'
            };
        },
        schoolDisplayName(){
            return this.schoolInfo.short_name || this.schoolInfo.name || this.cityName || '请选择学校';
        },
        schoolDisplayInitial(){
            const name = this.schoolDisplayName || '校';
            return String(name).trim().slice(0, 1) || '校';
        },
        homeTopbarRowStyle(){
            if(!this.topbarCapsuleReserve){
                return {};
            }
            return {
                paddingRight: this.topbarCapsuleReserve + 'px'
            };
        },
        homeTopNavStickyStyle(){
            return {
                top: this.topbarNavStickyTop + 'px'
            };
        },
        schoolLogoUrl(){
            return this.schoolTheme.logo || '';
        },
        schoolHeaderBgImage(){
            return this.schoolTheme.headerBgImage || '';
        },
        schoolHeroStyle(){
            const primary = this.schoolTheme.primary;
            const secondary = this.schoolTheme.secondary;
            const textColor = this.schoolTheme.textColor;
            const backgroundImage = this.schoolHeaderBgImage
                ? `linear-gradient(135deg, ${this.hexToRgba(primary, 0.94)} 0%, ${this.hexToRgba(secondary, 0.84)} 100%), url(${this.schoolHeaderBgImage})`
                : `linear-gradient(135deg, ${primary} 0%, ${secondary} 100%)`;
            return {
                '--school-header-primary': primary,
                '--school-header-secondary': secondary,
                '--school-header-text': textColor,
                backgroundColor: primary,
                backgroundImage: backgroundImage,
                backgroundSize: 'cover',
                backgroundPosition: 'center',
                backgroundRepeat: 'no-repeat'
            };
        }
  },
  methods:{
        resetFeedState(keepList = false){
            page = 1;
            if(!keepList){
                this.list = [];
                this.list1 = [];
                this.list2 = [];
            }
            this.totalPage = '';
            this.showNoResult = false;
        },
        applyHomeUserState(userInfo){
            this.user = userInfo || {};
            this.refreshAppTheme(this.user);
            this.user_id = this.user && this.user.id ? this.user.id : '';
            this.baseLogo = this.user && this.user.avatar ? this.user.avatar : this.baseLogo;
            if(this.user && this.user.school_id){
                this.cityName = this.user.school_name || '已绑定学校';
                this.typeDataLists();
                this.loadGroupQrcode(this.user.school_id || 0);
                this.loadHomeBanners(this.user.school_id || 0);
                this.syncTopbarStickyTop();
                return true;
            }
            this.cityName = '请选择学校';
            this.navData = [];
            this.homeBanners = [];
            this.groupQrcodeVisible = false;
            this.groupQrcode = null;
            return false;
        },
        initTopbarCapsuleReserve(){
            let reserve = 0;
            let stickyTop = 108;
            try {
                const systemInfo = uni.getSystemInfoSync ? uni.getSystemInfoSync() : null;
                if (typeof wx !== 'undefined' && wx.getMenuButtonBoundingClientRect && systemInfo && systemInfo.windowWidth) {
                    const rect = wx.getMenuButtonBoundingClientRect();
                    if (rect && rect.left) {
                        reserve = Math.max(110, Math.ceil(systemInfo.windowWidth - rect.left + 12));
                    }
                }
                if (systemInfo && systemInfo.windowWidth) {
                    const rpxUnit = systemInfo.windowWidth / 750;
                    const statusBarHeight = Number(systemInfo.statusBarHeight || 0);
                    const visibleHeight = Math.ceil((18 + 100 + 18) * rpxUnit);
                    stickyTop = Math.max(88, statusBarHeight + visibleHeight);
                }
            } catch (error) {}
            this.topbarCapsuleReserve = reserve;
            this.topbarNavStickyTop = stickyTop;
        },
        syncTopbarStickyTop(){
            this.$nextTick(() => {
                const query = uni.createSelectorQuery().in(this);
                query.select('.home-topbar').boundingClientRect();
                query.exec(res => {
                    const rect = res && res[0];
                    if(rect && rect.height){
                        this.topbarNavStickyTop = Math.ceil(rect.height);
                    }
                });
            });
        },
        hexToRgba(color, alpha){
            if(typeof color !== 'string'){
                return `rgba(106, 90, 249, ${alpha})`;
            }
            let hex = color.trim().replace('#', '');
            if(hex.length === 3){
                hex = hex.split('').map(item => item + item).join('');
            }
            if(!/^[0-9a-fA-F]{6}$/.test(hex)){
                return `rgba(106, 90, 249, ${alpha})`;
            }
            const red = parseInt(hex.slice(0, 2), 16);
            const green = parseInt(hex.slice(2, 4), 16);
            const blue = parseInt(hex.slice(4, 6), 16);
            return `rgba(${red}, ${green}, ${blue}, ${alpha})`;
        },
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
	  		const loadFeedList = () => {
	  			const keepList = Array.isArray(_this.list) && _this.list.length > 0;
	  			_this.resetFeedState(keepList);
	  			_this.getListHandle();
	  		};
	  		_this.$api.typeData(
	  		{type:0},
	  		data => {
	  			console.log(data);
	  			var res = Array.isArray(data.data) ? data.data : [];
	  			if (data.code == 1) {
	  				_this.navData = res;
	  				if(res.length > 0){
	  					_this.navId = res[0]['id'];
	  				}else{
	  					_this.navId = 0;
	  				}
	  				loadFeedList();
	  				return;
	  			}
	  			_this.navData = [];
	  			_this.navId = 0;
	  			loadFeedList();
	  		}
	  		)
	  },
	  getListHandle() {
		const currentUser = this.user && this.user.id ? this.user : this.$common.userInfo();
		if(currentUser){
			this.user = currentUser;
			this.user_id = currentUser.id;
			this.baseLogo = currentUser.avatar || this.baseLogo;
		}
		var cityNm=this.cityName;
		var schoolId = currentUser && currentUser.school_id ? currentUser.school_id : 0;
		if(schoolId>0){
			cityNm='';
		}else if(this.cityName=='全国' || this.cityName=='请选择学校'){
			cityNm='';
		}
	  	this.$api.indexData(
	  		{type:this.navId,keywords:this.keywords,location:cityNm,school_id:schoolId,page:page,limit:limit,user_id:this.user_id},
	  		data => {
	  			const payload = data && data.data ? data.data : {};
	  			const pageData = payload.data && Array.isArray(payload.data.data)
	  				? payload.data
	  				: (Array.isArray(payload.data)
	  					? {
	  						data: payload.data,
	  						per_page: payload.data.length,
	  						current_page: page,
	  						last_page: page
	  					}
	  					: null);
	  			if (data.code != 1 || !pageData || !Array.isArray(pageData.data)) {
				_this.$common.normalToShow(data.msg || '获取帖子失败');
				return;
			}
			var res = pageData;
			const currentPage = Number(res.current_page || page || 1);
			const lastPage = Number(res.last_page || currentPage || 1);
			this.per_page = Number(res.per_page || res.data.length || 0);
			this.current_page = currentPage;
			let arr=res.data.map((item, index)=>{
				const feedMedia = this.normalizeFeedMedia(item);
				return{
					...item,
					page:currentPage,
					feed_key: item.id ? ('feed-' + item.id) : ('feed-' + currentPage + '-' + index),
					feed_nickname: this.normalizeFeedNickname(item.nickname),
					feed_media: feedMedia,
					feed_text: item.text || item.content || '',
					feed_time_text: this.formatFeedTime(item.createtime),
					favorNum: Number(item.favorNum || 0),
					commentNum: Number(item.commentNum || 0)
				}
			})
	  			if(currentPage >= lastPage){
	  				_this.showNoResult=true;
	  			}
	  			page = currentPage + 1;
	  			_this.list = currentPage === 1 ? arr : _this.list.concat(arr);
				_this.totalPage = lastPage;
				_this.messageCount = Number(payload.msgCount || 0);
	  		}
	  		)
	  },
	  tabSelect(e){
	  	 _this.navIndex=e.currentTarget.dataset.index;
		   _this.navId=e.currentTarget.dataset.id;
		   page=1;
		   this.list = [];
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
	  normalizeFeedMedia(item){
	  	const urls = item && item.image_urlLists ? String(item.image_urlLists).split(',') : (item && item.image_url ? [item.image_url] : []);
	  	return urls.filter(Boolean).slice(0, 3);
	  },
	  normalizeFeedNickname(nickname){
	  	return String(nickname || '').replace(/(\.\.\.|…)+$/g, '').trim();
	  },
	  previewFeedImages(item, mediaIndex){
	  	const urls = item && Array.isArray(item.feed_media) ? item.feed_media : this.normalizeFeedMedia(item);
	  	if(!urls.length){
	  		return;
	  	}
	  	uni.previewImage({
	  		urls: urls,
	  		current: urls[mediaIndex] || urls[0]
	  	});
	  },
	  formatFeedTime(timestamp){
	  	const value = Number(timestamp || 0);
	  	if(!value){
	  		return '';
	  	}
	  	const now = Math.floor(Date.now() / 1000);
	  	const diff = Math.max(0, now - value);
	  	if(diff < 60){
	  		return '刚刚';
	  	}
	  	if(diff < 3600){
	  		return Math.max(1, Math.floor(diff / 60)) + '分钟前';
	  	}
	  	if(diff < 86400){
	  		return Math.max(1, Math.floor(diff / 3600)) + '小时前';
	  	}
	  	if(diff < 86400 * 30){
	  		return Math.max(1, Math.floor(diff / 86400)) + '天前';
	  	}
	  	if(diff < 86400 * 365){
	  		return Math.max(1, Math.floor(diff / (86400 * 30))) + '个月前';
	  	}
	  	return Math.max(1, Math.floor(diff / (86400 * 365))) + '年前';
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

	.contentWarp{ padding: 12rpx 0 120rpx; background-color: #f5f7fb;}
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
.home-topbar{
	position: sticky;
	top: 0;
	z-index: 40;
	padding: calc(var(--status-bar-height) + 18rpx) 0 18rpx;
	background: linear-gradient(135deg, #8fbff6 0%, #abd2ff 55%, #c9e1ff 100%);
	box-sizing: border-box;
	overflow: hidden;
}
.home-topbar-row{
	display: flex;
	align-items: center;
	min-height: 100rpx;
	padding: 0 24rpx;
	box-sizing: border-box;
}
.home-school-hero{
	flex: 1;
	min-width: 0;
	display: flex;
	align-items: center;
}
.home-school-logo-shell{
	width: 88rpx;
	height: 88rpx;
	padding: 8rpx;
	border-radius: 22rpx;
	background: rgba(255,255,255,0.95);
	box-sizing: border-box;
	box-shadow: 0 10rpx 24rpx rgba(46, 97, 170, 0.14);
	flex-shrink: 0;
}
.home-school-logo{
	width: 100%;
	height: 100%;
	border-radius: 16rpx;
	display: block;
}
.home-school-logo-fallback{
	width: 100%;
	height: 100%;
	border-radius: 16rpx;
	display: flex;
	align-items: center;
	justify-content: center;
	background: linear-gradient(135deg, #5d8fe0 0%, #3b6fd2 100%);
	color: #ffffff;
	font-size: 34rpx;
	font-weight: 700;
}
.home-school-main{
	min-width: 0;
	padding-left: 20rpx;
}
.home-school-name{
	display: block;
	overflow: hidden;
	text-overflow: ellipsis;
	white-space: nowrap;
	font-size: 46rpx;
	font-weight: 700;
	line-height: 1.2;
	color: var(--school-header-text, #111827);
}
.home-top-nav-sticky{
	position: sticky;
	z-index: 39;
	padding: 0;
	background: #ffffff;
	box-shadow: 0 8rpx 18rpx rgba(15, 23, 42, 0.04);
}
.home-top-nav{
	margin-top: 0;
	white-space: nowrap;
	height: 84rpx;
	padding: 0 24rpx;
	background: #ffffff;
	box-sizing: border-box;
}
.home-top-nav .cu-item{
	position: relative;
	display: inline-flex;
	align-items: center;
	justify-content: center;
	height: 84rpx;
	line-height: 84rpx;
	margin-right: 54rpx;
	padding: 0;
	min-width: 0;
	border-radius: 0;
	background: transparent;
	border: none;
	color: #5b616b;
	font-size: 34rpx;
	font-weight: 500;
	box-sizing: border-box;
}
.home-top-nav .cu-item.cur{
	background: transparent;
	border: none;
	color: #111827;
	font-weight: 700;
	box-shadow: none;
}
.home-top-nav .cu-item.cur::after{
	content: '';
	position: absolute;
	left: 0;
	right: 0;
	bottom: 12rpx;
	height: 6rpx;
	border-radius: 999rpx;
	background: #111827;
}
.home-banner-wrap{
	padding: 16rpx 24rpx 12rpx;
	background: #ffffff;
}.header_search{
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
.home-feed-list{
	padding: 12rpx 0 8rpx;
}
.feed-card{
	padding: 18rpx 24rpx 16rpx;
	margin-bottom: 14rpx;
	background: #ffffff;
	border-radius: 0;
	box-shadow: none;
	border-top: 1rpx solid #eef2f7;
	border-bottom: 1rpx solid #eef2f7;
}
.feed-card-head{
	display: flex;
	align-items: center;
	justify-content: space-between;
}
.feed-user{
	flex: 1;
	min-width: 0;
	display: flex;
	align-items: center;
}
.feed-user-avatar{
	width: 48rpx;
	height: 48rpx;
	border-radius: 12rpx;
	flex-shrink: 0;
	background: #f1f5f9;
}
.feed-user-main{
	min-width: 0;
	padding-left: 12rpx;
}
.feed-user-name{
	font-size: 28rpx;
	font-weight: 400;
	color: #334155;
	line-height: 1.2;
}
.feed-more{
	padding-left: 12rpx;
	font-size: 28rpx;
	color: #94a3b8;
}
.feed-card-body{
	padding-left: 58rpx;
	margin-top: 8rpx;
}
.feed-title{
	font-size: 28rpx;
	font-weight: 400;
	line-height: 1.45;
	color: #334155;
	word-break: break-word;
}
.feed-text{
	margin-top: 2rpx;
	font-size: 28rpx;
	line-height: 1.45;
	color: #334155;
	word-break: break-word;
}
.feed-media-grid{
	display: flex;
	flex-wrap: wrap;
	gap: 8rpx;
	margin-top: 8rpx;
}
.feed-media-item{
	position: relative;
	width: 132rpx;
	height: 132rpx;
	border-radius: 14rpx;
	overflow: hidden;
	background: #e5e7eb;
}
.feed-media-image{
	width: 100%;
	height: 100%;
	display: block;
}
.feed-video-mask{
	position: absolute;
	left: 50%;
	top: 50%;
	transform: translate(-50%, -50%);
	width: 56rpx;
	height: 56rpx;
	border-radius: 50%;
	background: rgba(15, 23, 42, 0.48);
	display: flex;
	align-items: center;
	justify-content: center;
	z-index: 2;
}
.feed-video-mask text{
	color: #fff;
	font-size: 30rpx;
}
.feed-card-foot{
	padding-left: 58rpx;
	margin-top: 12rpx;
	display: flex;
	align-items: center;
	justify-content: space-between;
}
.feed-card-meta{
	font-size: 20rpx;
	color: #9ca3af;
	line-height: 1.2;
}
.feed-card-actions{
	display: flex;
	align-items: center;
}
.feed-card-action{
	display: inline-flex;
	align-items: center;
	margin-left: 18rpx;
	font-size: 24rpx;
	color: #475569;
}
.feed-card-action text:first-child{
	margin-right: 6rpx;
	font-size: 26rpx;
}
.uinfo{ font-size:20rpx;}
</style>





