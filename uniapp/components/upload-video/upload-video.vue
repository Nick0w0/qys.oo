<template>
	<!-- 上传控件 -->
	<view class="itemImg uploadControl">
		<block v-if="videoList.length>0">
			<view class="uploadimg_list margin-bottom" v-for="(item,index) in videoList" :key='index'>
				<!-- <image :src="cndUrl+item" class="itemImg" mode="aspectFill"></image> -->
				<video :id="index" @play="playVideo(index)" @error="videoErrorCallback" :src="cndUrl+item" controls></video>
				<view class="deleteIcon" @click="deleteVideo(index)">X</view>
			</view>
		</block>
		<block v-if="videoList.length<maxCount">
			<view class="uploadImg_btn" @click="chooseVideo('')">
				<view class="">
					<image src="../../static/images/video.png" mode="aspectFill"></image>
					<text>视频</text>
				</view>
			</view>
		</block>
	</view>
</template>

<script>
	import {
		cndUrl,
		baseUrl,
		baseApiUrl
	} from '../../config/config.js';
	export default {
	  props: {
	    //服务返回回调的图片数组--回填
	    mode: {
	      type: Array,
	      default: function() {
	        return []
	      }
	    },
			type: {
				type:String,
				default: ''
			},
			maxCount: {
				type:Number,
				default: 5
			},
	  },
	  data() {
	    return {
				cndUrl:cndUrl,
	      videoList: [],
	      showList: [],
	      showUploadControl: true,
				uploadNum:5
	    }
	  },
	  watch: {
	    mode(v) {
	      this.init(v)
	    },
	  },
	  created() {
			
	  },
	  methods: {
			init(v) {
				if (this.mode.length != 0) {
					this.videoList = v;
					this.uploadNum = this.maxCount
				}		
				return
			},
			playVideo(index){
				this.$emit("playVideo",index,this.videoList) 
			},
			//视频播放错误时触发
			videoErrorCallback(e){
				console.log(e);
			},
	    chooseVideo(){
	    	let _this = this
				let userToken = '';
				let auth = this.$db.get("auth");
				userToken = auth.token;
	    	//let video = {tempFilePaths:[],tempFiles:[]}
				let video=[]
				console.log(this.uploadNum);
	    	uni.chooseVideo({
	    		count: this.uploadNum,
	    		sourceType: ['album', 'camera'],
	    		compressed:true,
	    		success: (res) => {
						uni.showLoading({
							title:'视频上传中'
						})
	    			video.push(res.tempFilePath)
						for(var i=0; i<video.length; i++){
							uni.uploadFile({
								url: baseApiUrl + 'common/upload?token='+userToken,
								filePath: video[i],
								fileType: 'video',
								name: 'file',
								headers: {
									'Accept': 'application/json',
									'Content-Type': 'multipart/form-data',
									'token': userToken
								},
								formData: {},
								success: (uploadFileRes) => {
									uni.hideLoading()
									uni.showToast({
										title:'上传完成',
										icon:"none"
									})
									var dataimg=JSON.parse(uploadFileRes.data);
									this.videoList.push(dataimg.data.url)
									this.$emit("chooseFile",this.videoList) 
								},
								fail: (error) => {
									if (error && error.response) {
										this.$common.showError(error.response);
									}
								},
								complete: () => {
									setTimeout(function () {
										uni.hideLoading();
									}, 250);
								},
							});
						}
	    		},
	    	})
	    },
			//删除视频
			deleteVideo(eq) {
			  uni.showModal({
			    title: '提示',
			    content: '您确定删除吗？',
			    success: (res)=> {
			      if (res.confirm) {
			       this.videoList.splice(eq, 1);
			       this.$emit("imgDelete", this.videoList);
			      }
			    }
			  });
			},
			
	  }
	}	
</script>

<style>
	/* 上传  str */
	.imglistbx {
		border-radius: 10px;
		/* box-shadow: 0 0 10px #ccc; */
	}
	.identification_card{position: relative;
	  width: 540rpx;
	  height: 200rpx;
		background: #f3f2f7;}
	.imglistItem {
	  position: relative;
		display: flex;
		align-items: center;
	  /* width: 120rpx;
	  height: 120rpx; */
		/* background: #f8f8f8; */
	}
	
	.column3 {
	  width: 33.3333%;
	  height: 160rpx;
	}
	
	.column4 {
	  width: 25%;
	  height: 130rpx;
	}
	
	.itemImg {
		border-radius: 5px;
	  margin: 0 auto;
	  display: block;
	  border-radius: 10rpx;
	}
	
	.cancelBtn {
	  position: absolute;
	  top: -10rpx;
	  right: 10rpx;
	}
	
	/* 上传控件 */
	.uploadControl {
	  font-size: 50rpx;
	  color: #888;
	}
	
	/*  上传  str end*/
	.clear {
	  clear: both;
	}
	.uploadImg_btn{width: 100%; height: 350rpx; background: #f3f2f7; border-radius: 5px; display: flex; align-items: center; justify-content: center;}
	.uploadImg_btn image{width: 100rpx; height: 100rpx; display: block; margin: 0 auto;}
	.uploadImg_btn text{display: block; color: #999; font-size: 30rpx; margin-top: 10rpx;text-align: center;}
	.uploadimg_list{width: 100%; height: 350rpx;position: relative;}
	.uploadimg_list video{width: 100%; height: 100%;}
	.deleteIcon{position: absolute; right: -15rpx; top: -15rpx; background: #ccc; width: 30rpx; height: 30rpx; border-radius: 30rpx; font-size: 20rpx; text-align: center; line-height: 30rpx; z-index: 10; color: #999;}
	
	.identification_card .uploadimg_list{width: 100%; height: 100%;}
	.identification_card .itemImg{width: 100%; height: 100%;}
	.identification_card .uploadImg_btn{width: auto; height: auto; display: block;}
	.commonStyle .itemImg{margin: 0;}
</style>