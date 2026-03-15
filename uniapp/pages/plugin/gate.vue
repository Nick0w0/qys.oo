<template>
  <view class="gate-page">
    <cu-custom bgColor="bg-gradual-purple">
      <block slot="backText">返回</block>
      <block slot="content">欢迎加入</block>
    </cu-custom>
    <view class="gate-hero bg-gradual-purple">
      <view class="gate-title">先确认你的学校</view>
      <view class="gate-subtitle">平台按学校隔离内容。完成登录并绑定学校后，才会展示本校帖子、评论、互动与消息。</view>
    </view>
    <view class="gate-card">
      <view class="gate-step">
        <view class="gate-step-index">1</view>
        <view class="gate-step-content">
          <view class="gate-step-title">登录账号</view>
          <view class="gate-step-desc">先完成登录，系统才能识别你的身份和学校归属。</view>
        </view>
      </view>
      <view class="gate-step">
        <view class="gate-step-index">2</view>
        <view class="gate-step-content">
          <view class="gate-step-title">选择并绑定学校</view>
          <view class="gate-step-desc">绑定后不可自行修改，如需变更请联系管理员处理。</view>
        </view>
      </view>
      <view class="gate-tip">{{ buttonText === '去选择学校' ? '你已登录，下一步只需绑定学校。' : '未登录时暂不开放内容浏览。' }}</view>
      <button class="cu-btn bg-purple round gate-btn" @tap="handlePrimary">{{ buttonText }}</button>
      <button class="cu-btn line-purple round gate-btn-secondary" @tap="goUserCenter">去我的页</button>
    </view>
  </view>
</template>

<script>
export default {
  data() {
    return {
      user: null,
      buttonText: '先登录并选择学校'
    }
  },
  onShow() {
    this.user = this.$db.get('user') || null;
    if (this.user && this.user.id) {
      if (this.user.school_id && String(this.user.school_locked) === '1') {
        uni.switchTab({ url: '/pages/index/index' });
        return;
      }
      this.buttonText = '去选择学校';
      return;
    }
    this.buttonText = '先登录并选择学校';
  },
  methods: {
    handlePrimary() {
      if (this.user && this.user.id) {
        this.$common.navigateTo('/pages/plugin/index');
        return;
      }
      this.$common.navigateTo('/pages/user/login');
    },
    goUserCenter() {
      uni.switchTab({ url: '/pages/user/index' });
    }
  }
}
</script>

<style lang="scss">
.gate-page {
  min-height: 100vh;
  background: #f6f7fb;
}
.gate-hero {
  padding: 48rpx 32rpx 80rpx;
}
.gate-title {
  color: #fff;
  font-size: 44rpx;
  font-weight: 600;
}
.gate-subtitle {
  margin-top: 18rpx;
  color: rgba(255,255,255,0.88);
  font-size: 28rpx;
  line-height: 1.7;
}
.gate-card {
  margin: -36rpx 24rpx 0;
  padding: 36rpx 28rpx;
  background: #fff;
  border-radius: 28rpx;
  box-shadow: 0 14rpx 36rpx rgba(93, 63, 211, 0.08);
}
.gate-step {
  display: flex;
  align-items: flex-start;
}
.gate-step + .gate-step {
  margin-top: 24rpx;
}
.gate-step-index {
  width: 44rpx;
  height: 44rpx;
  line-height: 44rpx;
  text-align: center;
  border-radius: 50%;
  background: #7b2cff;
  color: #fff;
  font-size: 24rpx;
  font-weight: 600;
  flex-shrink: 0;
  margin-right: 20rpx;
}
.gate-step-title {
  color: #222;
  font-size: 30rpx;
  font-weight: 600;
}
.gate-step-desc {
  margin-top: 8rpx;
  color: #666;
  font-size: 26rpx;
  line-height: 1.7;
}
.gate-tip {
  margin-top: 30rpx;
  padding: 20rpx 24rpx;
  background: #f6efff;
  color: #6d35d7;
  font-size: 26rpx;
  line-height: 1.6;
  border-radius: 18rpx;
}
.gate-btn {
  margin-top: 28rpx;
  width: 100%;
  height: 88rpx;
  line-height: 88rpx;
  font-size: 30rpx;
}
.gate-btn-secondary {
  margin-top: 20rpx;
  width: 100%;
  height: 84rpx;
  line-height: 84rpx;
  font-size: 28rpx;
}
</style>