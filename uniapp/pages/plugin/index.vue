<template>
	<view class="content" :style="themeVarsStyle">
		<cu-custom bgColor="bg-gradual-purple" :isBack="true">
			<block slot="backText">返回</block>
			<block slot="content">选择学校</block>
		</cu-custom>
		<view class="page-body">
			<view class="tips-box margin bg-white padding radius">
				<view class="text-bold text-red margin-bottom-sm">请确认你的学校</view>
				<view class="text-gray text-sm">学校一旦绑定成功，将无法自行修改，如需修改请联系管理员处理。</view>
			</view>
			<view class="filter-box margin bg-white padding radius">
				<view class="search-field">
					<text class="cuIcon-locationfill text-purple"></text>
					<input
						class="search-input"
						type="text"
						v-model="cityKeyword"
						placeholder="按城市筛选，如：广州"
						confirm-type="search"
						cursor-spacing="24"
						@confirm="fetchSchools"
					/>
				</view>
				<view class="search-field margin-top-sm">
					<text class="cuIcon-search text-purple"></text>
					<input
						class="search-input"
						type="text"
						v-model="keyword"
						placeholder="搜索学校名称或简称"
						confirm-type="search"
						cursor-spacing="24"
						@confirm="fetchSchools"
					/>
				</view>
				<view class="flex justify-between align-center margin-top">
					<button class="cu-btn line-purple filter-btn" @click="useCurrentCity">{{ currentCity ? '使用定位城市' : '获取定位城市' }}</button>
					<button class="cu-btn bg-gradual-purple filter-btn" @click="fetchSchools">搜索</button>
				</view>
			</view>
			<view class="margin-lr margin-bottom-sm text-sm text-gray" v-if="currentCity">
				当前定位城市：{{ currentCity }}
			</view>
			<view class="school-list margin-lr margin-bottom">
				<view class="school-item bg-white padding radius margin-bottom-sm" v-for="item in schoolList" :key="item.id" @tap="confirmBind(item)">
					<view class="text-bold">{{ item.name }}</view>
					<view class="text-gray text-sm" v-if="item.short_name">简称：{{ item.short_name }}</view>
					<view class="text-gray text-sm">{{ item.province }} {{ item.city }} {{ item.area }}</view>
					<view class="text-gray text-sm" v-if="item.address">{{ item.address }}</view>
				</view>
				<view class="text-center text-gray padding" v-if="loaded && schoolList.length === 0">暂无匹配学校</view>
			</view>
		</view>
	</view>
</template>

<script>
export default {
	data() {
		return {
			keyword: '',
			cityKeyword: '',
			currentCity: '',
			schoolList: [],
			loaded: false,
			user: null,
			locating: false,
		};
	},
	onLoad() {
		this.user = this.$db.get('user');
		if (!this.user || !this.user.id) {
			this.$common.navigateTo('/pages/user/login');
			return;
		}
		this.$api.refreshUser({}, res => {
			if (res.code === 1 && res.data && res.data.user) {
				this.user = res.data.user;
				this.refreshAppTheme(this.user);
				try {
					this.$db.set('auth', res.data.auth || {});
					this.$db.set('user', res.data.user || {});
				} catch (error) {}
			}
			if (this.user.school_id && String(this.user.school_locked) === '1') {
				this.$common.normalToShow('学校已绑定，如需修改请联系管理员');
				setTimeout(() => {
					uni.switchTab({ url: '/pages/index/index' });
				}, 400);
				return;
			}
			this.fetchSchools();
		});
	},
	methods: {
		fetchSchools() {
			this.$api.schoolListData({
				keyword: this.keyword,
				city: this.cityKeyword
			}, res => {
				this.loaded = true;
				if (res.code === 1) {
					this.schoolList = res.data.list || [];
				} else {
					this.schoolList = [];
					this.$common.normalToShow(res.msg || '获取学校失败');
				}
			});
		},
		getLocation() {
			if (this.locating) {
				return;
			}
			this.locating = true;
			uni.getLocation({
				type: 'gcj02',
				success: res => {
					const latlng = res.latitude + ',' + res.longitude;
					this.$api.locationAddress({ latlng }, data => {
						this.locating = false;
						if (data.code === 1) {
							this.currentCity = data.data.city || '';
							this.cityKeyword = this.currentCity;
							this.fetchSchools();
							return;
						}
						this.$common.normalToShow(data.msg || '定位城市获取失败');
					});
				},
				fail: () => {
					this.locating = false;
					this.$common.normalToShow('定位失败，请手动输入城市');
				}
			});
		},
		useCurrentCity() {
			if (this.currentCity) {
				this.cityKeyword = this.currentCity;
				this.fetchSchools();
				return;
			}
			this.getLocation();
		},
		confirmBind(item) {
			uni.showModal({
				title: '确认绑定学校',
				content: '确认绑定“' + item.name + '”吗？绑定后将无法自行修改，如需修改请联系管理员。',
				success: modalRes => {
					if (modalRes.confirm) {
						this.bindSchool(item.id, item.name);
					}
				}
			});
		},
		bindSchool(schoolId, schoolName) {
			this.$api.bindSchoolData({ school_id: schoolId }, res => {
				if (res.code === 1) {
					this.$db.set('auth', res.data.auth);
					this.$db.set('user', res.data.user);
					this.refreshAppTheme(res.data.user);
					this.$common.successToShow('学校绑定成功');
					const pages = getCurrentPages();
					const prevPage = pages[pages.length - 2];
					if (prevPage && prevPage.$vm) {
						prevPage.$vm.cityName = res.data.user.school_name || schoolName;
					}
					setTimeout(() => {
						uni.navigateBack({
							delta: 1,
							fail: () => {
								uni.switchTab({ url: '/pages/index/index' });
							}
						});
					}, 400);
				} else {
					this.$common.errorToShow(res.msg || '学校绑定失败');
				}
			});
		}
	}
};
</script>

<style lang="scss">
.content {
	min-height: 100vh;
	background: #f6f7fb;
}
.page-body {
	padding-top: 12rpx;
}
.tips-box {
	border-left: 6rpx solid #e54d42;
}
.filter-box {
	position: relative;
}
.search-field {
	display: flex;
	align-items: center;
	padding: 0 24rpx;
	min-height: 88rpx;
	background: #f7f8fa;
	border: 1rpx solid #eceef5;
	border-radius: 14rpx;
}
.search-field text {
	font-size: 32rpx;
	margin-right: 16rpx;
}
.search-input {
	flex: 1;
	height: 88rpx;
	font-size: 28rpx;
	color: #222;
}
.filter-btn {
	min-width: 220rpx;
}
.school-item {
	box-shadow: 0 6rpx 18rpx rgba(0, 0, 0, 0.04);
}
</style>



