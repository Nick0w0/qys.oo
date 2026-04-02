<template>
  <view class="content" :style="themeVarsStyle">
	<view class="home-topbar" :style="schoolHeroStyle">
		<view class="home-topbar-row" :style="homeTopbarRowStyle">
			<view class="home-school-hero">
				<view class="home-school-logo-shell">
					<image v-if="schoolLogoUrl" class="home-school-logo" :src="schoolLogoUrl" mode="aspectFill"></image>
					<view v-else class="home-school-logo-fallback">{{ schoolDisplayInitial }}</view>
				</view>
				<view class="home-school-main" :style="homeSchoolMainStyle">
					<view class="home-school-name-row">
						<text class="home-school-name">{{ schoolDisplayName }}</text>
						<text class="cuIcon-search home-school-search" @tap.stop="openSearch"></text>
					</view>
				</view>
			</view>
		</view>
	</view>
	<view class="home-banner-wrap" v-if="homeBanners.length > 0">
		<swiper class="home-banner-swiper" :indicator-dots="homeBanners.length > 1" indicator-color="rgba(255,255,255,0.5)" indicator-active-color="#ffffff" :autoplay="homeBanners.length > 1" :interval="4000" :duration="500" circular>
			<swiper-item v-for="(item,index) in homeBanners" :key="item.banner_key">
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
				<view class="feed-card" v-for="(item, index) in list" :key="item.feed_key">
					<view class="feed-card-head">
						<view class="feed-user" :data-id="item.id" @tap="detailTab">
							<image class="feed-user-avatar" v-if="item.avatar!=''" :src="item.avatar" mode="aspectFill"></image>
							<image class="feed-user-avatar" v-else src="../../static/images/avatar.png" mode="aspectFill"></image>
							<view class="feed-user-main">
								<view class="feed-user-meta">
									<view class="feed-user-name">{{item.feed_nickname || item.nickname}}</view>
									<view class="feed-user-tag" v-if="item.feed_identity_label">{{ item.feed_identity_label }}</view>
								</view>
								<view class="feed-user-time">{{ item.feed_time_text }}</view>
							</view>
						</view>
						<view class="feed-card-tools">
							<text class="cuIcon-moreandroid feed-more" @tap.stop="openFeedActions(item)"></text>
						</view>
					</view>
					<view class="feed-card-body" :data-id="item.id" @tap="detailTab">
						<view class="feed-title" v-if="item.feed_display_title">{{ item.feed_display_title }}</view>
						<view class="feed-text" v-if="item.feed_display_text">{{ item.feed_display_text }}</view>
						<view class="feed-media-grid" v-if="item.feed_media && item.feed_media.length > 0">
							<view class="feed-media-item" v-for="(img, mediaIndex) in item.feed_media" :key="mediaIndex" @tap.stop="previewFeedImages(item, mediaIndex)">
								<view class="feed-video-mask" v-if="item.type=='video' && mediaIndex===0"><text class="cuIcon-playfill"></text></view>
								<image class="feed-media-image" :src="img" mode="aspectFill"></image>
							</view>
						</view>
					</view>
					<view class="feed-card-foot">
						<view class="feed-card-actions">
							<view class="feed-card-action">
								<text class="cuIcon-comment feed-card-action__icon"></text>
								<text class="feed-card-action__count" v-if="item.commentNum > 0">{{item.commentNum}}</text>
							</view>
							<view class="feed-card-action">
								<text class="cuIcon-appreciate feed-card-action__icon"></text>
								<text class="feed-card-action__count" v-if="item.favorNum > 0">{{item.favorNum}}</text>
							</view>
						</view>
					</view>
				</view>
			</view>
		</view>
	<view class="text-center text-gray padding" v-if="list.length==0">暂无更多数据...</view>
	<view class="text-center text-gray padding-tb-sm" :class="showNoResult?'show':'hide'" v-if="list.length>0">我是有底线的...</view>

	<view class="feed-action-mask" v-if="feedActionVisible" @tap="closeFeedActions">
		<view class="feed-action-sheet" @tap.stop="">
			<view class="feed-action-sheet__panel">
				<view class="feed-action-sheet__grid">
					<view class="feed-action-sheet__item" @tap="copyFeedContent">
						<view class="feed-action-sheet__icon">
							<text class="cuIcon-copy"></text>
						</view>
						<view class="feed-action-sheet__label">复制</view>
					</view>
					<view class="feed-action-sheet__item" @tap="reportFeedContent">
						<view class="feed-action-sheet__icon">
							<text class="cuIcon-warn"></text>
						</view>
						<view class="feed-action-sheet__label">举报</view>
					</view>
				</view>
			</view>
			<view class="feed-action-sheet__cancel" @tap="closeFeedActions">取消</view>
		</view>
	</view>

	<view class="report-reason-mask" v-if="reportReasonVisible" @tap="closeReportReasonSheet">
		<view class="report-reason-sheet" @tap.stop="">
			<view class="report-reason-sheet__panel">
				<view class="report-reason-sheet__title">选择举报原因</view>
				<view class="report-reason-sheet__grid">
					<view
						class="report-reason-sheet__item"
						v-for="(reason, reasonIndex) in reportReasonOptions"
						:key="reason.key"
						@tap="submitReportReason(reason)"
					>{{ reason.label }}</view>
				</view>
			</view>
			<view class="report-reason-sheet__cancel" @tap="closeReportReasonSheet">取消</view>
		</view>
	</view>

	<view class="cu-modal" :class="groupQrcodeVisible ? 'show' : ''" @tap="hideGroupQrcode">
		<view class="cu-dialog group-qrcode-dialog" @tap.stop="">
			<view class="group-qrcode-header bg-white">
				<view class="group-qrcode-title">{{ groupQrcode ? groupQrcode.title : '进群提醒' }}</view>
				<view class="group-qrcode-close" @tap="hideGroupQrcode"><text class="cuIcon-close text-gray"></text></view>
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
        feedActionVisible:false,
        currentFeedActionItem:null,
        reportReasonVisible:false,
        pendingReportDiscoverId:0,
        reportReasonOptions:[
            { key:'reason-1', label:'垃圾广告' },
            { key:'reason-2', label:'色情低俗' },
            { key:'reason-3', label:'人身攻击' },
            { key:'reason-4', label:'违法违规' },
            { key:'reason-5', label:'其他' }
        ],
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
            const fullName = String(this.schoolInfo.name || '').trim();
            const shortName = String(this.schoolInfo.short_name || '').trim();
            if(fullName){
                if(shortName && shortName !== fullName && fullName.length > 6){
                    return shortName;
                }
                return fullName;
            }
            return shortName || this.cityName || '请选择学校';
        },
        schoolDisplayInitial(){
            const name = this.schoolDisplayName || '校';
            return String(name).trim().slice(0, 1) || '校';
        },
        homeTopbarRowStyle(){
            if(!this.topbarCapsuleReserve){
                return {
                    paddingRight: '248rpx'
                };
            }
            return {
                paddingRight: (this.topbarCapsuleReserve + 24) + 'px'
            };
        },
        homeTopNavStickyStyle(){
            return {
                top: this.topbarNavStickyTop + 'px'
            };
        },
        homeSchoolMainStyle(){
            if(this.topbarCapsuleReserve){
                return {
                    paddingRight: (this.topbarCapsuleReserve + 40) + 'px'
                };
            }
            return {
                paddingRight: '220rpx'
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
            return {
                '--school-header-primary': primary,
                '--school-header-secondary': secondary,
                '--school-header-text': textColor
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
                let systemInfo = null;
                if (typeof wx !== 'undefined' && typeof wx.getWindowInfo === 'function') {
                    systemInfo = wx.getWindowInfo() || null;
                } else {
                    systemInfo = uni.getSystemInfoSync ? uni.getSystemInfoSync() : null;
                }
                if (typeof wx !== 'undefined' && wx.getMenuButtonBoundingClientRect && systemInfo && systemInfo.windowWidth) {
                    const rect = wx.getMenuButtonBoundingClientRect();
                    if (rect && rect.left) {
                        reserve = Math.max(176, Math.ceil(systemInfo.windowWidth - rect.left + 52));
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
			this.hideGroupQrcode();
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
					this.homeBanners = Array.isArray(res.data.list) ? res.data.list.map((item, index) => ({
						...item,
						banner_key:item.id ? ('banner-' + item.id) : ('banner-' + index)
					})) : [];
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
				const feedCopy = this.normalizeFeedCopy(item);
				return{
					...item,
					page:currentPage,
					feed_key: item.id ? ('feed-' + item.id) : ('feed-' + currentPage + '-' + index),
					feed_nickname: this.normalizeFeedNickname(item.nickname),
					feed_identity_label: this.normalizeFeedIdentity(item),
					feed_media: feedMedia,
					feed_display_title: feedCopy.title,
					feed_display_text: feedCopy.text,
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
	  normalizeFeedIdentity(item){
	  	const candidates = [
	  		item && item.identity_text,
	  		item && item.grade_name,
	  		item && item.user_type_text,
	  		item && item.school_name,
	  		item && item.school_short_name,
	  		item && item.role_name
	  	];
	  	for(let i = 0; i < candidates.length; i++){
	  		const value = String(candidates[i] || '').trim();
	  		if(value){
	  			return value.length > 8 ? value.slice(0, 8) + '…' : value;
	  		}
	  	}
	  	return '';
	  },
	  normalizeFeedCopy(item){
	  	const rawTitle = String((item && item.title) || '').trim();
	  	const rawContent = String((item && item.content) || '').trim();
	  	const rawSummary = String((item && item.text) || '').trim();
	  	const bodyText = rawContent || rawSummary;
	  	const normalizedTitle = rawTitle.replace(/\s+/g, '');
	  	const normalizedBody = bodyText.replace(/\s+/g, '');
	  	if(!bodyText){
	  		return {
	  			title: '',
	  			text: rawTitle
	  		};
	  	}
	  	if(rawTitle && normalizedTitle && normalizedTitle !== normalizedBody){
	  		return {
	  			title: rawTitle,
	  			text: bodyText
	  		};
	  	}
	  	return {
	  		title: '',
	  		text: bodyText
	  	};
	  },
	  buildFeedActionCopyText(item){
	  	if(!item){
	  		return '';
	  	}
	  	const feedCopy = this.normalizeFeedCopy(item);
	  	const lines = [];
	  	if(feedCopy.title){
	  		lines.push(feedCopy.title);
	  	}
	  	if(feedCopy.text){
	  		lines.push(feedCopy.text);
	  	}
	  	if(!lines.length && item.feed_display_title){
	  		lines.push(String(item.feed_display_title).trim());
	  	}
	  	if(!lines.length && item.feed_display_text){
	  		lines.push(String(item.feed_display_text).trim());
	  	}
	  	return lines.filter(Boolean).join('\n');
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
		openFeedActions(item){
			this.currentFeedActionItem = item || null;
			this.feedActionVisible = true;
		},
		closeFeedActions(){
			this.feedActionVisible = false;
			this.currentFeedActionItem = null;
		},
		openReportReasonSheet(discoverId){
			this.pendingReportDiscoverId = Number(discoverId || 0);
			if(!this.pendingReportDiscoverId){
				this.$common.errorToShow('帖子信息异常');
				return;
			}
			this.reportReasonVisible = true;
		},
		closeReportReasonSheet(){
			this.reportReasonVisible = false;
			this.pendingReportDiscoverId = 0;
		},
		copyFeedContent(){
			const copyText = this.buildFeedActionCopyText(this.currentFeedActionItem);
			if(!copyText){
				this.$common.normalToShow('这条帖子暂无可复制内容');
				this.closeFeedActions();
				return;
			}
			uni.setClipboardData({
				data: copyText,
				success: () => {
					this.$common.successToShow('内容已复制');
				},
				fail: () => {
					this.$common.errorToShow('复制失败，请重试');
				},
				complete: () => {
					this.closeFeedActions();
				}
			});
		},
		reportFeedContent(){
			const item = this.currentFeedActionItem || {};
			const discoverId = Number(item.id || 0);
			this.closeFeedActions();
			if(!discoverId){
				this.$common.errorToShow('帖子信息异常');
				return;
			}
			this.openReportReasonSheet(discoverId);
		},
		submitReportReason(reason){
			const discoverId = Number(this.pendingReportDiscoverId || 0);
			const finalReason = String(reason && reason.label ? reason.label : reason || '').trim();
			if(!discoverId || !finalReason){
				this.closeReportReasonSheet();
				this.$common.errorToShow('举报信息异常');
				return;
			}
			this.$api.reportData({
				discover_id: discoverId,
				reason: finalReason
			}, data => {
				this.closeReportReasonSheet();
				if (data.code == 1) {
					this.$common.successToShow(data.msg || '举报已提交');
				}else{
					this.$common.errorToShow(data.msg || '举报失败，请重试');
				}
			});
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
	  openSearch(){
		this.$common.navigateTo('/pages/index/search');
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
.group-qrcode-header{
	position: relative;
	padding: 28rpx 84rpx 18rpx 32rpx;
	border-radius: 24rpx 24rpx 0 0;
}
.group-qrcode-title{
	font-size: 36rpx;
	line-height: 1.35;
	font-weight: 500;
	color: #4b5563;
	text-align: center;
	word-break: break-word;
	display: -webkit-box;
	-webkit-box-orient: vertical;
	-webkit-line-clamp: 2;
	overflow: hidden;
}
.group-qrcode-close{
	position: absolute;
	top: 24rpx;
	right: 20rpx;
	width: 56rpx;
	height: 56rpx;
	display: flex;
	align-items: center;
	justify-content: center;
	font-size: 34rpx;
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
	padding: 20rpx 24rpx 58rpx;
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
	z-index: 52;
	padding: calc(var(--status-bar-height) + 14rpx) 0 0;
	box-sizing: border-box;
	overflow: visible;
	background: #ffffff;
	background-image: none;
}
.home-topbar::after{
	content: '';
	position: absolute;
	left: 0;
	right: 0;
	bottom: -2rpx;
	height: 12rpx;
	background: #ffffff;
	pointer-events: none;
}
.home-topbar-row{
	display: flex;
	align-items: center;
	min-height: 84rpx;
	padding: 0 24rpx 6rpx;
	box-sizing: border-box;
	background: #ffffff;
}
.home-school-hero{
	flex: 1;
	min-width: 0;
	display: flex;
	align-items: center;
	padding: 0;
}
.home-school-logo-shell{
	width: 74rpx;
	height: 74rpx;
	padding: 6rpx;
	border-radius: 20rpx;
	background: rgba(255,255,255,0.92);
	border: 1rpx solid rgba(143, 191, 246, 0.18);
	box-sizing: border-box;
	box-shadow: 0 8rpx 18rpx rgba(15, 23, 42, 0.06);
	flex-shrink: 0;
}
.home-school-logo{
	width: 100%;
	height: 100%;
	border-radius: 14rpx;
	display: block;
}
.home-school-logo-fallback{
	width: 100%;
	height: 100%;
	border-radius: 14rpx;
	display: flex;
	align-items: center;
	justify-content: center;
	background: linear-gradient(135deg, var(--school-header-primary) 0%, var(--school-header-secondary) 100%);
	color: #ffffff;
	font-size: 30rpx;
	font-weight: 700;
}
.home-school-main{
	flex: 1;
	min-width: 0;
	padding-left: 18rpx;
	padding-top: 10rpx;
}
.home-school-name-row{
	display: inline-flex;
	align-items: center;
	width: auto;
	max-width: 100%;
	min-width: 0;
	box-sizing: border-box;
}
.home-school-name{
	display: block;
	flex: 0 1 auto;
	max-width: 100%;
	overflow: hidden;
	text-overflow: ellipsis;
	white-space: nowrap;
	font-size: 28rpx;
	font-weight: 500;
	letter-spacing: 0.5rpx;
	line-height: 1.16;
	color: var(--school-header-text, #111827);
	transform: translateY(0rpx);
}
.home-school-search{
	position: static;
	transform: none;
	margin-left: 16rpx;
	font-size: 26rpx;
	line-height: 1;
	color: var(--school-header-text, #111827);
	opacity: 0.45;
	flex-shrink: 0;
}
.home-top-nav-sticky{
	position: sticky;
	z-index: 51;
	padding: 0;
	margin-top: -2rpx;
	background: #ffffff;
	border-top: 1rpx solid rgba(255,255,255,0.96);
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
	margin-right: 32rpx;
	padding: 0;
	min-width: 0;
	border-radius: 0;
	background: transparent;
	border: none;
	color: #5b616b;
	font-size: 22rpx;
	font-weight: 500;
	letter-spacing: 1rpx;
	box-sizing: border-box;
}
.home-top-nav .cu-item.cur{
	background: transparent;
	border: none;
	color: #111827;
	font-weight: 600;
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
	padding: 16rpx 24rpx 10rpx;
	background: #ffffff;
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
	padding: 0;
}
.feed-card{
	padding: 28rpx 26rpx 26rpx;
	margin-bottom: 14rpx;
	background: #ffffff;
	border-radius: 0;
	box-shadow: none;
	border: none;
}
.feed-card-head{
	display: flex;
	align-items: flex-start;
	justify-content: space-between;
}
.feed-card-tools{
	display: flex;
	align-items: flex-start;
	flex-shrink: 0;
	margin-left: 16rpx;
}
.feed-user{
	flex: 1;
	min-width: 0;
	display: flex;
	align-items: flex-start;
}
.feed-user-avatar{
	width: 54rpx;
	height: 54rpx;
	border-radius: 50%;
	flex-shrink: 0;
	background: #f1f5f9;
}
.feed-user-main{
	min-width: 0;
	padding-left: 16rpx;
	padding-top: 2rpx;
}
.feed-user-meta{
	display: flex;
	align-items: center;
	gap: 10rpx;
	min-width: 0;
}
.feed-user-name{
	display: block;
	width: 118%;
	max-width: none;
	font-size: 26rpx;
	font-weight: 700;
	color: #6a7e96;
	line-height: 1.1;
	letter-spacing: .6rpx;
	-webkit-transform: scale(0.88);
	transform: scale(0.88);
	-webkit-transform-origin: left center;
	transform-origin: left center;
	margin-left: -4rpx;
}
.feed-user-tag{
	flex-shrink: 0;
	max-width: 120rpx;
	padding: 1rpx 6rpx;
	border-radius: 999rpx;
	background: #eef5ff;
	font-size: 14rpx;
	line-height: 1.2;
	color: #7ba7d9;
	overflow: hidden;
	text-overflow: ellipsis;
	white-space: nowrap;
}
.feed-user-time{
	display: block;
	width: 118%;
	margin-top: -4rpx;
	font-size: 20rpx;
	line-height: 1.1;
	color: #b3bac6;
	-webkit-transform: scale(0.88);
	transform: scale(0.88);
	-webkit-transform-origin: left center;
	transform-origin: left center;
	margin-left: -4rpx;
}
.feed-more{
	padding-top: 8rpx;
	padding-left: 0;
	font-size: 30rpx;
	line-height: 1;
	color: #b6bcc7;
}
.feed-action-mask{
	position: fixed;
	left: 0;
	right: 0;
	top: 0;
	bottom: 0;
	background: rgba(15, 23, 42, 0.4);
	z-index: 1999;
	display: flex;
	align-items: flex-end;
}
.feed-action-sheet{
	width: 100%;
	padding: 0 12rpx calc(env(safe-area-inset-bottom) + 8rpx);
	box-sizing: border-box;
}
.feed-action-sheet__panel{
	background: #ffffff;
	border-radius: 28rpx 28rpx 0 0;
	padding: 24rpx 24rpx 18rpx;
}
.feed-action-sheet__grid{
	display: flex;
	align-items: flex-start;
	gap: 18rpx;
}
.feed-action-sheet__item{
	display: flex;
	flex-direction: column;
	align-items: center;
	justify-content: center;
	width: 128rpx;
}
.feed-action-sheet__icon{
	width: 88rpx;
	height: 88rpx;
	border-radius: 22rpx;
	background: #f8fafc;
	display: flex;
	align-items: center;
	justify-content: center;
	font-size: 42rpx;
	color: #111827;
	box-shadow: inset 0 0 0 1rpx #eef2f7;
}
.feed-action-sheet__label{
	margin-top: 12rpx;
	font-size: 24rpx;
	color: #5b6475;
}
.feed-action-sheet__cancel{
	margin-top: 0;
	background: #ffffff;
	border-radius: 0 0 28rpx 28rpx;
	text-align: center;
	font-size: 30rpx;
	font-weight: 600;
	color: #7c8798;
	line-height: 88rpx;
	border-top: 1rpx solid #eef2f7;
}
.report-reason-mask{
	position: fixed;
	left: 0;
	right: 0;
	top: 0;
	bottom: 0;
	background: rgba(15, 23, 42, 0.32);
	z-index: 2000;
	display: flex;
	align-items: flex-end;
}
.report-reason-sheet{
	width: 100%;
	padding: 0 16rpx calc(env(safe-area-inset-bottom) + 12rpx);
	box-sizing: border-box;
}
.report-reason-sheet__panel{
	background: #ffffff;
	border-radius: 28rpx;
	padding: 26rpx 24rpx 22rpx;
	box-shadow: 0 18rpx 48rpx rgba(15, 23, 42, 0.12);
}
.report-reason-sheet__title{
	font-size: 28rpx;
	font-weight: 600;
	color: #1f2937;
	text-align: center;
}
.report-reason-sheet__grid{
	display: grid;
	grid-template-columns: repeat(2, minmax(0, 1fr));
	gap: 16rpx;
	margin-top: 22rpx;
}
.report-reason-sheet__item{
	height: 84rpx;
	border-radius: 20rpx;
	background: #f8fafc;
	box-shadow: inset 0 0 0 1rpx #e8edf5;
	display: flex;
	align-items: center;
	justify-content: center;
	font-size: 28rpx;
	color: #334155;
}
.report-reason-sheet__cancel{
	margin-top: 12rpx;
	height: 84rpx;
	border-radius: 24rpx;
	background: #ffffff;
	display: flex;
	align-items: center;
	justify-content: center;
	font-size: 30rpx;
	font-weight: 600;
	color: #7c8798;
}
.feed-card-body{
	padding-left: 0;
	margin-top: 4rpx;
}
.feed-title{
	display: block;
	width: 112%;
	font-size: 25rpx;
	font-weight: 500;
	line-height: 1.65;
	letter-spacing: .8rpx;
	color: #4b5563;
	word-break: break-word;
	-webkit-transform: scale(0.9);
	transform: scale(0.9);
	-webkit-transform-origin: left top;
	transform-origin: left top;
	margin-bottom: -6rpx;
}
.feed-text{
	display: block;
	width: 112%;
	margin-top: 0;
	font-size: 29rpx;
	font-weight: 400;
	font-family: "Heiti SC", "Microsoft YaHei", sans-serif;
	line-height: 1.65;
	letter-spacing: .8rpx;
	color: #4b5563;
	word-break: break-word;
	white-space: pre-wrap;
	-webkit-transform: scale(0.9);
	transform: scale(0.9);
	-webkit-transform-origin: left top;
	transform-origin: left top;
	margin-bottom: -2rpx;
}
.feed-media-grid{
	display: flex;
	flex-wrap: wrap;
	gap: 10rpx;
	margin-top: 0;
}
.feed-media-item{
	position: relative;
	width: 148rpx;
	height: 148rpx;
	border-radius: 16rpx;
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
	padding-left: 0;
	margin-top: 24rpx;
	display: flex;
	align-items: center;
	justify-content: flex-end;
}
.feed-card-actions{
	display: flex;
	align-items: center;
	gap: 22rpx;
}
.feed-card-action{
	display: inline-flex;
	align-items: center;
	justify-content: flex-start;
	min-width: 72rpx;
	margin-left: 0;
	font-size: 26rpx;
	letter-spacing: 0;
	color: #5b6472;
}
.feed-card-action__icon{
	margin-right: 6rpx;
	font-size: 32rpx;
	line-height: 1;
}
.feed-card-action__count{
	min-width: 16rpx;
	font-size: 22rpx;
	line-height: 1;
	text-align: left;
}
.uinfo{ font-size:20rpx;}
</style>





