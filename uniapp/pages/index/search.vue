<template>
	<view class="search-page" :style="themeVarsStyle">
		<cu-custom bgColor="bg-white" :isBack="true"><block slot="backText">返回</block><block slot="content">搜索</block></cu-custom>
		<view class="search-page-body">
			<view class="search-bar">
				<text class="cuIcon-search search-bar-icon"></text>
				<input class="search-bar-input" v-model="keywords" :focus="true" confirm-type="search" placeholder="搜索帖子标题或正文" @confirm="submitSearch" />
				<text class="search-bar-action" @tap="submitSearch">搜索</text>
			</view>
			<view class="search-page-tip">{{ searchScopeText }}</view>
			<view class="search-empty" v-if="!searched && !loading">输入关键词开始搜索</view>
			<view class="search-empty" v-else-if="searched && !loading && list.length===0">暂无相关帖子</view>
			<view class="search-loading" v-if="loading && list.length===0">搜索中...</view>
			<view class="search-feed-list" v-if="list.length>0">
				<view class="search-feed-card" v-for="(item,index) in list" :key="item.feed_key || index">
					<view class="search-feed-head">
						<view class="search-feed-user" :data-id="item.id" @tap="detailTab">
							<image class="search-feed-avatar" v-if="item.avatar" :src="item.avatar" mode="aspectFill"></image>
							<image class="search-feed-avatar" v-else src="../../static/images/avatar.png" mode="aspectFill"></image>
							<view class="search-feed-user-main"><text class="search-feed-name">{{item.feed_nickname || item.nickname}}</text></view>
						</view>
					</view>
					<view class="search-feed-body" :data-id="item.id" @tap="detailTab">
						<view class="search-feed-text" v-if="item.title">{{ item.title }}</view>
						<view class="search-feed-text" v-if="item.feed_text">{{ item.feed_text }}</view>
						<view class="search-feed-media" v-if="item.feed_media && item.feed_media.length>0">
							<view class="search-feed-media-item" v-for="(img,mediaIndex) in item.feed_media" :key="item.feed_key + '-img-' + mediaIndex" @tap.stop="previewFeedImages(item, mediaIndex)">
								<image :src="img" mode="aspectFill"></image>
							</view>
						</view>
					</view>
					<view class="search-feed-foot">
						<text class="search-feed-time">{{ item.feed_time_text }}</text>
						<view class="search-feed-actions">
							<view class="search-feed-action"><text class="cuIcon-appreciatefill"></text><text>{{item.favorNum}}</text></view>
							<view class="search-feed-action"><text class="cuIcon-messagefill"></text><text>{{item.commentNum}}</text></view>
						</view>
					</view>
				</view>
			</view>
		</view>
	</view>
</template>

<script>
export default {
	data() {
		return { keywords:'', list:[], user:null, user_id:'', schoolId:0, schoolName:'', currentPage:1, lastPage:1, loading:false, searched:false };
	},
	computed: {
		searchScopeText() {
			return this.schoolName ? ('当前学校：' + this.schoolName) : '当前范围：全部学校';
		}
	},
	onLoad(e) {
		this.keywords = e && e.keyword ? decodeURIComponent(e.keyword) : '';
		this.user = this.$common.userInfo() || null;
		if (this.user && this.user.id) {
			this.user_id = this.user.id;
			this.schoolId = Number(this.user.school_id || 0);
			this.schoolName = this.user.school_name || '';
		}
		if (this.user_id) {
			this.$api.refreshUser({}, res => {
				if (res.code === 1 && res.data && res.data.user) {
					this.user = res.data.user;
					this.user_id = res.data.user.id || '';
					this.schoolId = Number(res.data.user.school_id || 0);
					this.schoolName = res.data.user.school_name || this.schoolName;
					this.refreshAppTheme(res.data.user);
				}
			});
		}
		if (this.keywords) this.fetchList(true);
	},
	onReachBottom() {
		if (!this.loading && this.searched && this.currentPage <= this.lastPage) this.fetchList(false);
	},
	methods: {
		submitSearch() {
			this.keywords = String(this.keywords || '').trim();
			if (!this.keywords) return this.$common.normalToShow('请输入搜索关键词');
			this.fetchList(true);
		},
		fetchList(reset) {
			if (reset) { this.currentPage = 1; this.lastPage = 1; this.list = []; }
			this.loading = true;
			this.$api.indexData({ type:0, keywords:this.keywords, location:'', school_id:this.schoolId, page:this.currentPage, limit:10, user_id:this.user_id }, data => {
				this.loading = false; this.searched = true;
				const pageData = data && data.data && data.data.data && Array.isArray(data.data.data.data) ? data.data.data : null;
				if (data.code !== 1 || !pageData) { this.list = reset ? [] : this.list; return this.$common.normalToShow(data.msg || '获取帖子失败'); }
				const rows = (pageData.data || []).map((item, index) => ({ ...item, feed_key:(item.id ? 'search-' + item.id : 'search-' + this.currentPage + '-' + index), feed_nickname:this.normalizeFeedNickname(item.nickname), feed_media:this.normalizeFeedMedia(item), feed_text:item.text || item.content || '', feed_time_text:this.formatFeedTime(item.createtime), favorNum:Number(item.favorNum || 0), commentNum:Number(item.commentNum || 0) }));
				this.lastPage = Number(pageData.last_page || this.currentPage || 1);
				this.currentPage = Number(pageData.current_page || this.currentPage || 1) + 1;
				this.list = reset ? rows : this.list.concat(rows);
			});
		},
		normalizeFeedMedia(item) { const urls = item && item.image_urlLists ? String(item.image_urlLists).split(',') : (item && item.image_url ? [item.image_url] : []); return urls.filter(Boolean).slice(0, 3); },
		normalizeFeedNickname(nickname) { return String(nickname || '').replace(/(\.\.\.|…)+$/g, '').trim(); },
		formatFeedTime(timestamp) { const value = Number(timestamp || 0); if (!value) return ''; const now = Math.floor(Date.now() / 1000); const diff = Math.max(0, now - value); if (diff < 60) return '刚刚'; if (diff < 3600) return Math.max(1, Math.floor(diff / 60)) + '分钟前'; if (diff < 86400) return Math.max(1, Math.floor(diff / 3600)) + '小时前'; if (diff < 86400 * 30) return Math.max(1, Math.floor(diff / 86400)) + '天前'; if (diff < 86400 * 365) return Math.max(1, Math.floor(diff / (86400 * 30))) + '个月前'; return Math.max(1, Math.floor(diff / (86400 * 365))) + '年前'; },
		detailTab(e) { this.$common.navigateTo('detail?id=' + e.currentTarget.dataset.id); },
		previewFeedImages(item, mediaIndex) { const urls = Array.isArray(item.feed_media) ? item.feed_media : []; if (!urls.length) return; uni.previewImage({ urls, current: urls[mediaIndex] || urls[0] }); }
	}
}
</script>

<style lang="scss">
.search-page{ min-height:100vh; background:#f6f8fc; }
.search-page-body{ padding:20rpx 24rpx 32rpx; }
.search-bar{ display:flex; align-items:center; padding:0 20rpx; height:76rpx; border-radius:999rpx; background:#fff; border:1rpx solid #e2e8f0; }
.search-bar-icon{ font-size:28rpx; color:#94a3b8; }
.search-bar-input{ flex:1; height:76rpx; padding:0 16rpx; font-size:28rpx; color:#0f172a; }
.search-bar-action{ flex-shrink:0; font-size:24rpx; color:var(--school-theme-primary); }
.search-page-tip,.search-empty,.search-loading{ padding:24rpx 6rpx 0; font-size:22rpx; color:#94a3b8; }
.search-feed-list{ padding-top:20rpx; }
.search-feed-card{ padding:20rpx 24rpx 18rpx; margin-bottom:16rpx; background:#fff; border-radius:20rpx; }
.search-feed-head,.search-feed-foot,.search-feed-actions,.search-feed-user,.search-feed-action,.search-feed-media{ display:flex; align-items:center; }
.search-feed-user{ flex:1; min-width:0; }
.search-feed-avatar{ width:48rpx; height:48rpx; border-radius:12rpx; background:#f1f5f9; }
.search-feed-user-main{ min-width:0; padding-left:12rpx; }
.search-feed-name{ font-size:22rpx; font-weight:700; color:#334155; line-height:1.1; }
.search-feed-body{ padding-left:60rpx; margin-top:8rpx; }
.search-feed-text{ font-size:29rpx; line-height:1.6; color:#334155; word-break:break-word; }
.search-feed-media{ gap:8rpx; margin-top:10rpx; flex-wrap:wrap; }
.search-feed-media-item{ width:132rpx; height:132rpx; border-radius:14rpx; overflow:hidden; background:#e5e7eb; }
.search-feed-media-item image{ width:100%; height:100%; display:block; }
.search-feed-foot{ justify-content:space-between; padding-left:60rpx; margin-top:12rpx; }
.search-feed-time{ font-size:14rpx; color:#c0c8d4; line-height:1.1; }
.search-feed-actions{ justify-content:flex-end; }
.search-feed-action{ margin-left:18rpx; font-size:24rpx; color:#475569; }
.search-feed-action text:first-child{ margin-right:6rpx; font-size:26rpx; }
</style>
