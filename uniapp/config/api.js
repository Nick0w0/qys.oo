import {
	baseUrl,
	baseApiUrl
} from './config.js';
import * as common from './common.js' //引入common
import * as db from './db.js' //引入common
// 需要登陆的，都写到这里，否则就是不需要登陆的接口
const methodsToken = ['profile','refreshUser','changeMobile','publish','dolike','doComment','doAttention','doAttentionLists','showMessageLists','doMessageRead','doCommentSub','doLikeLists','doCommentLists','userDataLists','doMyDiscoverLists','delData','attentionData','bindSchool','index'];
const post = (method, data, callback,type) => {
	let userToken = '';
	const auth = db.get("auth");
	const nowdate = (new Date()) / 1000;
	const hasValidAuth = !!(auth && auth.token && auth.createtime + auth.expires_in >= nowdate);
	if (hasValidAuth) {
		userToken = auth.token;
	}
	console.log("method: ",method);
	if (methodsToken.indexOf(method) >= 0) {
		common.isLogin();
		if (!hasValidAuth) {
			common.toLogin();
			return false;
		}
	}
	
	if(type){
		method =  type + '/' + method
	}else{
		method = '/' + method
	}
	uni.request({
		url: baseApiUrl + method,
		data: data,
		header: {
			'Accept': 'application/json',
			'Content-Type': 'application/x-www-form-urlencoded',
			'token': userToken,
		},
		method: 'POST',
		success: (response) => {
			const result = response.data
			if (result.msg == 'Please login' || result.msg == '请登陆') {
				db.del("user");
				db.del("auth");
				console.log('未登陆')
				uni.showToast({
					title: result.msg,
					icon: 'none',
					duration: 2000,
					complete: function() {
						uni.reLaunch({
							url: '/pages/index/index',
						})
					}
				});
			}
			callback(result);
		},
		fail: (error) => {
			if (error && error.response) {
				showError(error.response);
			}
		},
	});
}

// 上传图片
export const uploadImage = (method , data = {} , callback , num = 9 ,type) => {
	if(type){
		method =  type + '/' + method
	}else{
		method =method
	}
	let userToken = '';
	let auth = db.get("auth");
	userToken = auth.token;
	uni.chooseImage({
		count:num,
		success: (res) => {
			uni.showLoading({
				title: '上传中...'
			});
			let tempFilePaths = res.tempFilePaths
			for (var i = 0; i < tempFilePaths.length; i++) {
				data.file = tempFilePaths[i]
				uni.uploadFile({
					url: baseApiUrl + method,
					filePath: tempFilePaths[i],
					fileType: 'image',
					name: 'file',
					headers: {
						'Accept': 'application/json',
						'Content-Type': 'multipart/form-data',
						'token': userToken
					},
					formData: data,
					success: (uploadFileRes) => {
						callback(JSON.parse(uploadFileRes.data))
					},
					fail: (error) => {
						if (error && error.response) {
							common.showError(error.response);
						}
					},
					complete: () => {
						setTimeout(function () {
							uni.hideLoading();
						}, 250);
					},
				});
			}
		}
	});
}

const get = (url, callback) => {
	uni.showLoading({
		title: '加载中'
	});
	uni.request({
		url: url,
		header: {
			'Accept': 'application/json',
			'Content-Type': 'application/x-www-form-urlencoded', //自定义请求头信息
		},
		method: 'GET',
		success: (response) => {
			callback(response.data);
		},
		fail: (error) => {
			if (error && error.response) {
				showError(error.response);
			}
		},
		complete: () => {
			setTimeout(function() {
				uni.hideLoading();
			}, 250);
		}
	});
}

const showError = error => {
	let errorMsg = ''
	switch (error.status) {
		case 400:
			errorMsg = '请求参数错误'
			break
		case 401:
			errorMsg = '未授权，请登录'
			break
		case 403:
			errorMsg = '跨域拒绝访问'
			break
		case 404:
			errorMsg = `请求地址出错: ${error.config.url}`
			break
		case 408:
			errorMsg = '请求超时'
			break
		case 500:
			errorMsg = '服务器内部错误'
			break
		case 501:
			errorMsg = '服务未实现'
			break
		case 502:
			errorMsg = '网关错误'
			break
		case 503:
			errorMsg = '服务不可用'
			break
		case 504:
			errorMsg = '网关超时'
			break
		case 505:
			errorMsg = 'HTTP版本不受支持'
			break
		default:
			errorMsg = error.msg
			break
	}
	uni.showToast({
		title: errorMsg,
		icon: 'none',
		duration: 2000
	});
}
// 登录
export const third = (data, callback) => post('third', data, callback,'discover/User');
// 用户绑定手机
export const bindphone = (data, callback) => post('bind', data, callback,'discover/User');
// 修改用户信息
export const profile = (data, callback) => post('profile', data, callback,'discover/User');
// 发送验证码
export const sendSmsVerify = (data, callback) => post('sendSmsVerify', data, callback,'discover/User');
// 刷新用户
export const refreshUser = (data, callback) => post('refreshUser', data, callback,'discover/User');
// 注册
export const register = (data, callback) => post('register', data, callback,'discover/User');
// 登录
export const login = (data, callback) => post('login', data, callback,'discover/User');
// 退出登录
export const logout = (data, callback) => post('logout', data, callback,'discover/User');
// 上传头像
export const upload = (data, callback) => post('upload', data, callback,'discover/Ajax');
export const schoolListData = (data, callback) => post('schoolList', data, callback,'discover/User');
export const bindSchoolData = (data, callback) => post('bindSchool', data, callback,'discover/User');
export const groupQrcodeData = (data, callback) => post('groupQrcode', data, callback,'discover/User');
export const homeBannerData = (data, callback) => post('homeBanner', data, callback,'discover/User');
export const homeBannerClickData = (data, callback) => post('homeBannerClick', data, callback,'discover/User');

// 绑定手机
export const changeMobile = (data, callback) => post('changeMobile', data, callback,'discover/User');

 /**
     * 获取首页数据
     * @param string $keywords     关键字
     * @param string $type         分类
     * @param string $location     定位
     * @param string $page         页码
     * @param string $limit        每页数量
     */
export const indexData = (data, callback) => post('index', data, callback,'discover/Common');

// 首页分类
export const typeData = (data, callback) => post('type', data, callback,'discover/Common');
// 发现详情
export const detailData = (data, callback) => post('detail', data, callback,'discover/Common');
// 举报动态
export const reportData = (data, callback) => post('report', data, callback,'discover/Common');
/**
 * 发布 动态
 * @param string $title        标题
 * @param string $content      内容
 * @param string $coverimage   封面图 
 * @param string $coverimages  组图
 * @param string $tag_ids      标签
 * @param string $top_ids      话题 //非必填
 * @param string $city         省市区
 * @param string $address      具体地址（非必填）
 * @param string $latlng        经纬度
 * @param string $iscommentdata 是否开启评论:1=开启,2=关闭
 */
export const publishData = (data, callback) => post('publish', data, callback,'discover/Common');
/**
 * 点赞
 *
 * @param string $discover_id 发现id
 * @param string $comment_id 评论id
 * @param string $type 点赞类型 1 作品点赞，  2，评论点赞
 */
export const dolikeData = (data, callback) => post('dolike', data, callback,'discover/Common');
/**
 * 评论
 *
 * @param string $discover_id 发现id
 * @param string $comment_id 评论id
 * @param string $content 评论内容
 * @param string $type 评论类型 1 作品评论，  2，评论回复
 */ 
export const doCommentData = (data, callback) => post('doComment', data, callback,'discover/Common');
/**
* 关注某人
*
* @param string $attention_id 关注人id
* @param string $discover_id 作品id
*/ 
export const doAttentionData = (data, callback) => post('doAttention', data, callback,'discover/Common');
/**
 * 评论列表
 *
 * @param string $discover_id
 * @param string $page 第几页，1开始 
 * @param string $limit 每一页显示多少数量
 * 
 */ 
export const showCommentListsData = (data, callback) => post('showCommentLists', data, callback,'discover/Common');
/**
* 消息列表
*
* @param string $type 消息类型 0 全部消息  消息类型:1=作品被点赞,2=评论被点赞,3=作品被评论,4=被关注,5=评论被回复,6=备用
* @param string $page 第几页，1开始 
* @param string $limit 每一页显示多少数量
* 
*/ 
export const showMessageListsData = (data, callback) => post('showMessageLists', data, callback,'discover/Common');
/**
* 消息已读操作
* @param string $ids 消息ids，多条 1,2,3,4,5,6,7
*/ 
export const doMessageReadData = (data, callback) => post('doMessageRead', data, callback,'discover/Common');
/**
* 消息已读操作
* @param string $ids 消息ids，多条 1,2,3,4,5,6,7
*/ 
export const doCommentSubData = (data, callback) => post('doCommentSub', data, callback,'discover/Common');
/**
* 作品被点赞列表
* @param string $id 传作品id 则查询该作品对应的点赞，否则就是该用户的全部点赞数据
*/ 
export const doLikeListsData = (data, callback) => post('doLikeLists', data, callback,'discover/Common');
/**
* 人被关注列表
* @param string $page 页数
* @param string $limit 数量
* @param string $type 1我关注的，2关注我的
*/
export const doAttentionListsData = (data, callback) => post('doAttentionLists', data, callback,'discover/Common');
/**
* 作品被评论的列表
* @param string $id 传作品id 则查询该作品对应的评论列表，否则就是该用户的全部评论数据
* @param string $page 页数
* @param string $limit 数量
*/ 
export const doCommentListsData = (data, callback) => post('doCommentLists', data, callback,'discover/Common');
/*个人中心展示数据*/
export const userDataListsData = (data, callback) => post('userDataLists', data, callback,'discover/Common');

export const doMyDiscoverListsData = (data, callback) => post('doMyDiscoverLists', data, callback,'discover/Common');

export const delData = (data, callback) => post('delData', data, callback,'discover/Common');
/**
* 获取我关注的用户的最新
*/
export const attentionData = (data, callback) => post('attentionData', data, callback,'discover/Common');
/**
* 获取我关注的用户的最新
*/
export const locationAddress = (data, callback) => post('locationData', data, callback,'discover/Common');

