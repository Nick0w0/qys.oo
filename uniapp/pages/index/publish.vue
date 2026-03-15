<template>
	<view>
		<cu-custom bgColor="bg-gradual-purple">
			<block slot="backText">返回</block>
			<block slot="content">发布动态</block>
		</cu-custom>
	
			<view class="cu-form-group margin-top">
				<input placeholder="请输入动态标题" name="input" @input="textareaAInput"></input>
			</view>
			
			<!-- !!!!! placeholder 在ios表现有偏移 建议使用 第一种样式 -->
			<view class="cu-form-group margin-top">
				<textarea maxlength="-1" :disabled="modalName!=null" @input="textareaBInput" placeholder="写点什么吧"></textarea>
			</view>
			
			<view class="cu-form-group margin-top" @tap="showModal" data-target="ChooseModal">
				<view class="title">加标签</view>
				<view class="action">
					<block v-for="(item,index) in labelList" :key="index" v-if="labelList.length>0">
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
					<block v-if="labelList.length==0">
						<text class="text-grey ">请选择标签</text>
					</block>
					<block v-if="labelList.length>3">
						<view class="cu-tag bg-white">...</view>
					</block>
				</view>
				<text class="cuIcon-right text-gray"></text>
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
			
			<view class="cu-bar bg-white margin-top">
				<view class="action">
					图片上传
				</view>
				<view class="action">
					{{imgList.length}}/{{maxCount}}
				</view>
			</view>
			<view class="cu-form-group">
				<view class="grid col-4 grid-square flex-sub">
					<view class="bg-img" v-for="(item,index) in imgList" :key="index" @tap="ViewImage" :data-url="imgList[index]">
					 <image :src="imgList[index]" mode="aspectFill"></image>
						<view class="cu-tag bg-red" @tap.stop="DelImg" :data-index="index">
							<text class='cuIcon-close'></text>
						</view>
						<view class="cu-tag text-white"  :class="blockIndex==index?'selectPic':''" style="bottom: 0; top: auto;" @tap.stop="blockImg" :data-index="index">
							<text class='cuIcon-paintfill' v-if="blockIndex==index">{{blockTitle}}</text>
							<text class='cuIcon-paintfill' v-else>{{blockTitle2}}</text>
						</view>
					</view>
					<view class="solids" @tap="ChooseImage" v-if="imgList.length<9">
						<text class='cuIcon-cameraadd'></text>
					</view>
				</view>
			</view>
			<view class="cu-bar bg-white margin-top">
				<view class="action">
					视频上传
				</view>
			</view>
			<view class="margin-top padding-lr bg-white">
				<uploadVideo :mode="videoList" :maxCount="1" @chooseFile="chooseFile"></uploadVideo>
			</view>
			<view class="cu-form-group margin-top">
				<view class="title">开启评论</view>
				<switch @change="SwitchA" :class="SwitchA?'checked':''" :checked="switchA?true:false"></switch>
			</view>
			<view class="cu-form-group margin-top">
				<view class="title">展示位置</view>
				<switch @change="SwitchB" :class="switchB?'checked':''" :checked="switchB?true:false"></switch>
			</view>
			<view class="cu-form-group margin-top" @tap="chooseLocation" data-target="ChooseModal" v-if="switchB">
				<view class="title">位置信息</view>
				<view class="action flex-wrap">
						<block v-if="addresses!=''">
							<view class="cu-tag round bg-blue light" style="max-width: 300rpx; padding: 0 10rpx; text-overflow: ellipsis; white-space: nowrap; overflow: hidden; ">{{addresses}}</view>
						</block>
						<block v-else>
							<view class="cu-tag round bg-blue light">请选择显示位置</view>
						</block>
				</view>
				<text class="cuIcon-right text-gray"></text>
			</view>
			
				<view class="cu-bar btn-group margin-top">
					<!-- <button class="cu-btn bg-green shadow-blur round">保存</button> -->
					<button class="cu-btn bg-purple shadow-blur round" @click="publishForm()"><text class="cuIcon-cameraadd"></text></text> 发布动态</button>
				</view>

		<colorbar footerTab="2" :messageCount='messageCount'></colorbar>
	</view>
</template>

<script>
	var _this;
	import colorbar from "@/components/color-bar.vue";
	import uploadVideo from "@/components/upload-video/upload-video.vue";
	import {
		baseUrl,
		baseApiUrl
	} from '../../config/config.js';
	export default {
		components: {colorbar,uploadVideo },
		data() {
			return {
				index: -1,
				blockTitle:'默认封面图',
				blockTitle2:'设为封面图',
				switchA: true,
				switchB: false,
				checkbox: [],
				imgList: [],
				imgList2:'',
				shortUrl:[],
				shortUrl2:'',
				blockIndex:0,//设置第几个为封面图
				modalName: null,
				textareaAValue: '',
				textareaBValue: '',
				labelList:[],
				selectList:[],
				user:[],
				maxCount:9,
				messageCount:0,
				latlng:'',
				addresses:'',
				tag_ids:'',
				publishSwitch:true,//防止重复点击
				videoList:[],
				shortImgUrl:[]
				
			};
		},
		mounted() {
			_this=this;
			_this.typeDataLists();
		},
		onLoad() {
			for(var j=0; j<this.checkbox.length; j++){
				if(this.checkbox[j].checked){
					this.labelList.push(this.checkbox[j])
				}
			}
			
		},
		onShow() {
			this.user = this.$common.userInfo();
			console.log("this.user: ",this.user);
			if (typeof(this.user)== "undefined" || this.user=='' ||  this.user==null) {
				this.$common.navigateTo('/pages/user/login');
			}else{
				this.$api.refreshUser(
				{},
				val => {
					this.user=val.data.user;
					this.auth=val.data.auth;
					this.messageCount=val.data.msgCount;
					if(!this.user.school_id){
						this.$common.normalToShow('请先绑定学校后再发布');
						setTimeout(() => {
							this.$common.navigateTo('/pages/plugin/index');
						}, 300);
					}
				})
			}
			
		},
		methods: {
			chooseFile(list){
				let videoUrl=list[0]
				this.shortUrl.push(videoUrl)
			},
			//分类数据
			typeDataLists(){
					_this.$api.typeData(
					{type:-1},
					data => {
						console.log(data);
						var res=data.data;
						if (data.code == 1) {
						 _this.checkbox=res;	
						}
					}
					)  
			},
			//判断是否授权打开定位
			isLocation:function(){
				uni.authorize({
				    scope: 'scope.userLocation',
				    success() {
				        uni.getLocation()
				    }
				})
			},
			getLocation:function(){
				var that=this;
				uni.getLocation({
				    type: 'gcj02',
				    success: function (res) {
						that.longitude = res.longitude;
						that.latitude = res.latitude;
						console.log("res: ",res);
				        console.log('当前位置的经度：' + res.longitude);
				        console.log('当前位置的纬度：' + res.latitude);
						that.chooseLocation();
				    },
					error:function(res){
						that.isLocation();
						console.log("res: ",res);
					}
				});
			},
			chooseLocation(){
				var that=this;
				uni.chooseLocation({
				    success: function (res) {
						// console.log("res: ",res);
				  //       console.log('位置名称：' + res.name);
				  //       console.log('详细地址：' + res.address);
				  //       console.log('纬度：' + res.latitude);
				  //       console.log('经度：' + res.longitude);
						that.addresses=res.name;
						that.latlng=res.latitude+','+res.longitude;
						console.log("that.addresses: ",that.addresses);
						console.log("that.latlng: ",that.latlng);
				    }
				});
			},
			//分类数据
			publishForm(){
				    if(this.textareaAValue==''){
						_this.$common.normalToShow('请输入动态标题')
						return false;
					}
					if(this.textareaBValue==''){
						_this.$common.normalToShow('请输入动态内容')
						return false;
					}
					if(this.tag_ids==''){
						_this.$common.normalToShow('请选择标签')
						return false;
					}
					if(this.imgList.length==0){
						_this.$common.normalToShow('请至少上传一张图片')
						return false;
					}
					if(this.imgList2==''){
						_this.$common.normalToShow('请选择封面图')
						return false;
					}

					if(this.switchB){
						if(this.addresses=='' || this.latlng==''){
							_this.$common.normalToShow('您已开启位置展示，请选择位置')
							return false;
						}
					}
					if(this.user && !this.user.school_id){
						_this.$common.normalToShow('请先绑定学校后再发布')
						return false;
					}
					if(this.publishSwitch){
						this.publishSwitch=false;
						this.$api.publishData(
						{title:this.textareaAValue,
							description:this.textareaBValue,
							content:this.textareaBValue,
							coverimage:this.shortUrl2,
							coverimages:this.shortUrl,
							tag_ids:this.tag_ids,
							city:'',
							address:this.addresses,
							latlng:this.latlng,
							iscommentdata:this.switchA?1:2,
							adddata:this.switchB?1:2,
							},
							data => {
								_this.publishSwitch=true;
								console.log(data);
								if (data.code == 1) {
									_this.$common.successToShow(data.msg,function(){
										_this.$common.navigateTo('index');
									});
								}else{
									_this.$common.errorToShow(data.msg);
								}
							}
							)  
					}
			},
			//确定按钮
			confirmBtn(){
				this.labelList=[];
				this.labelList=this.selectList;
				var tags=[];
				if(this.labelList.length>0){
					for (let i = 0, lenI = this.labelList.length; i < lenI; ++i) {
						 tags[i]=this.labelList[i].id;
					}
					this.tag_ids=tags.join();
				}
			    console.log("this.tag_ids: ",this.tag_ids);
				this.hideModal();
			},
			showModal(e) {
				this.modalName = e.currentTarget.dataset.target
			},
			hideModal(e) {
				this.modalName = null
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
			SwitchA(e) {
				this.switchA = e.detail.value
			},
			SwitchB(e) {
				this.switchB = e.detail.value
				if(this.switchB){
					//获取定位
					this.getLocation();
				}
			},

			RadioChange(e) {
				this.radio = e.detail.value
			},
			CheckboxChange(e) {
				var items = this.checkbox,
					values = e.detail.value;
				for (var i = 0, lenI = items.length; i < lenI; ++i) {
					items[i].checked = false;
					for (var j = 0, lenJ = values.length; j < lenJ; ++j) {
						if (items[i].value == values[j]) {
							items[i].checked = true;
							break
						}
					}
				}
			},
			ChooseImage() {
				var that=this;
				let userToken = '';
				let auth = this.$db.get("auth");
				userToken = auth.token;
				console.log("userToken: ",userToken);
				uni.chooseImage({
					count: Number(this.maxCount)-this.imgList.length, //默认9
					sizeType: ['original', 'compressed'], //可以指定是原图还是压缩图，默认二者都有
					sourceType: ['album'], //从相册选择
					success: (res) => {
						 const tempFilePaths = res.tempFilePaths;
	
							for(var i=0; i<tempFilePaths.length; i++){
									uni.uploadFile({
										url: baseApiUrl + 'common/upload?token='+userToken,
										filePath: tempFilePaths[i],
										fileType: 'image',
										name: 'file',
										headers: {
											'Accept': 'application/json',
											'Content-Type': 'multipart/form-data',
											'token': userToken
										},
										formData: {},
										success: (uploadFileRes) => {
											console.log('JSON.parse(uploadFileRes.data)--------=111111=============',JSON.parse(uploadFileRes.data))
											var dataimg=JSON.parse(uploadFileRes.data);
											//单独图片的数组
											this.shortImgUrl.push(dataimg.data.url);
											this.imgList.push(dataimg.data.fullurl);
											this.shortUrl.push(dataimg.data.url);
											
											console.log(this.imgList);
											var imgLength=this.imgList.length;
											console.log('====='+imgLength);
											this.maxCountNum=9-imgLength;
											
											if(this.imgList2=='' && this.imgList.length==1){
												this.imgList2=this.imgList[0];
												this.shortUrl2=this.shortImgUrl[0];
											}
											
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
									//
								}
							//this.imgList = this.imgList.concat(res.tempFilePaths)
							
						
					}
				});
			},
			ViewImage(e) {
				uni.previewImage({
					urls: this.imgList,
					current: e.currentTarget.dataset.url
				});
			},
			DelImg(e) {
				var index=e.currentTarget.dataset.index
				uni.showModal({
					title: '删除图片',
					content: '确定要删除这张图片吗？',
					cancelText: '取消',
					confirmText: '删除',
					success: res => {
						if (res.confirm) {
							this.imgList.splice(index, 1)
							if(index==this.blockIndex){
								this.blockIndex=0;
							}
						}
					}
				})
			},
			
			blockImg(e) {
				var index=e.currentTarget.dataset.index;
				if(index==this.blockIndex){
					return false;
				}
				uni.showModal({
					title: '设为封面图',
					content: '确定要将这张图片设为封面图吗？',
					cancelText: '取消',
					confirmText: '确定',
					success: res => {
						if (res.confirm) {
							
							this.imgList2=this.imgList[index];
							this.shortUrl2=this.shortUrl[index];
							this.blockIndex=index;
							console.log("this.imgList2: ",this.imgList2);
						}
					}
				})
			},
			textareaAInput(e) {
				this.textareaAValue = e.detail.value
			},
			textareaBInput(e) {
				this.textareaBValue = e.detail.value
			}
		}
	}
</script>

<style>
	.cu-form-group .title {
		min-width: calc(4em + 15px);
	}
	.selectPic{ color:#FFBC30}
</style>


