
<template>
  <view class="content" :style="themeVarsStyle">
	  <cu-custom bgColor="bg-gradual-purple" :isBack="false"><block slot="backText">返回</block><block slot="content">瀑布流种草</block></cu-custom>
    <waterfallsFlowNav
      ref="waterfalls_flow_nav"
      v-model="navIndex"
      :navData="navData"
      @add-data="getListHandle"
	  @nav-click='navClick'
	  @image-click='imageClick'
    >
      <!-- #ifndef  MP-WEIXIN -->
      <template v-slot:default="item">
        <view class="cnt">
          <view class="title">{{ item.title }}</view>
          <view class="text">{{ item.text }}</view>
		  <view class="user flex_layout">
		  	<view><image :src="item.avatar"></image>{{item.nickname}}</view>
		  	<view>
				<view class="text-gray text-sm">
					<!-- <text class="cuIcon-attentionfill margin-lr-xs"></text> 10 -->
					<text class="cuIcon-appreciatefill margin-lr-xs"></text>{{item.favorNum}}
					<text class="cuIcon-messagefill margin-lr-xs"></text> {{item.commentNum}}
				</view>
			</view>
		  </view>
        </view>
      </template>
      <!-- #endif -->
    </waterfallsFlowNav>
	  
	  <!--自定义底部tab组件-->
		<colorbar footerTab="0" :messageCount="messageCount"></colorbar>

  </view>
</template>
<script>
import waterfallsFlowNav from "@/components/maramlee-waterfalls-flow-nav/maramlee-waterfalls-flow-nav.vue";
import colorbar from "@/components/color-bar.vue"
var _this;
var page=0,limit=10,reachbottom = false;
export default {
  components: { waterfallsFlowNav,colorbar },
  data() {
    return {
      navIndex: 0,
      navData: [
        { key: "one", label: "推荐" },
        { key: "two", label: "运动" },
      
      ],
	  navId:'',
	  searchValue:'',
	  keywords:'"今日份小可爱"',
	  messageCount:0
    };
  },
  /**
   * 上拉加载
   * 这很重要
   */
  mounted() {
  	_this = this;
	 _this.typeDataLists();
  },
  onReachBottom() {
    this.getListHandle();
  },
  onLoad() {
	page=1;
  },
  onShow() {
  	this.user = this.$common.userInfo();
  	console.log("this.user: ",this.user);
  	if (typeof(this.user)== "undefined" || this.user=='' ||  this.user==null) {
  		//this.$common.navigateTo('login');
  	}else{
  		this.$api.refreshUser(
  		{},
  		val => {
  			this.user = val.data.user;
  			this.refreshAppTheme(this.user);
  			this.auth=val.data.auth;
  			this.messageCount=val.data.msgCount;
  		})
  	}
  },
  methods: {

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
					_this.navId=res[0]['key'];
					console.log("_this.navId: ",_this.navId);
				}
	  			}
	  		}
	  		)  
	  },
	  //详细数据
	  // detailDataLists(){
	  // 		this.$api.detailData(
	  // 		{id:1},
	  // 		data => {
	  // 			console.log(data);
	  // 			var res=data.data;
	  // 			if (data.code == 1) {
	  				
	  // 			}
	  // 		}
	  // 		)  
	  // },
    getListHandle() {
		this.$api.indexData(
			{type:this.navId,page:page+1},
			data => {
				console.log(data);
				var res=data.data;
				if (data.code == 1) {
					if(res.total_page>page+1){
						page++;
					}
					console.log("----page: ",page);
					
					 const mockData=res;
					 console.log("mockData: ",mockData);
					 _this.$refs.waterfalls_flow_nav.successSetData(
					   mockData.list,
					   mockData.total_page
					 );
				}else{
					_this.$refs.waterfalls_flow_nav.failMoreBack();
				}
			}
			)  
      
    },
	navClick(key,index){
		 _this.navIndex=index;
		  page=0;
		  this.navId=key;
		  console.log(key);
		  console.log("index: ",index); 
		  console.log(this.navData);
		 _this.$refs.waterfalls_flow_nav.initNavLists(this.navData);
		 // console.log("key: ",key);
		 //_this.$refs.waterfalls_flow_nav.getList(true);
		  
	},
	imageClick(e){
		console.log("e: ",e);
		this.$common.navigateTo('detail?id='+e.id);
	}
  },
  
};
</script>
<style>
page {
  background-color: #fff;
}
</style>
<style lang="scss">
.flex_layout{
	display: flex;
	justify-content: space-between;
	align-items: center;
}
.user{ padding: 20rpx 0 0;
	 font-size: 22rpx;
	 height: 50rpx; 
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
  .cnt {
    padding: 10px;
    .title {
      font-size: 16px;
    }
    .text {
      font-size: 14px;
      margin-top: 5px;
    }
  }
}

</style>



