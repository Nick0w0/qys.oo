<template>
  <view class="gate-page" :style="themeVarsStyle">
    <cu-custom bgColor="bg-white">
      <block slot="backText">返回</block>
      <block slot="content">欢迎加入</block>
    </cu-custom>

    <view class="gate-body">
      <view class="gate-logo-circle">
        <text class="cuIcon-homefill"></text>
      </view>

      <view class="gate-title">请选择学校后开始使用</view>

      <view class="gate-action">
        <button class="cu-btn bg-purple round gate-btn-primary" @tap="handlePrimary">{{ buttonText }}</button>
      </view>
    </view>
  </view>
</template>

<script>
import { hasBoundSchool, refreshWechatCode, getWechatReadyState, requestWechatLogin } from '../../config/wechat-auth.js';

export default {
  data() {
    return {
      user: null,
      buttonText: '登录并选择学校',
      code: '',
      wechatLoading: false
    }
  },
  onShow() {
    this.user = this.$db.get('user') || null;
    if (this.user && this.user.id) {
      this.syncUserState();
      return;
    }
    this.updateButtonText();
    // #ifdef MP-WEIXIN
    this.refreshWxCode();
    // #endif
  },
  methods: {
    syncUserState() {
      this.$api.refreshUser({}, res => {
        if (res.code === 1 && res.data) {
          this.user = res.data.user || this.user;
          this.refreshAppTheme(this.user);
          try {
            this.$db.set('auth', res.data.auth || {});
            this.$db.set('user', res.data.user || {});
          } catch (error) {}
          if (this.user && this.user.id && hasBoundSchool(this.user)) {
            this.goHome();
            return;
          }
        }
        this.updateButtonText();
        // #ifdef MP-WEIXIN
        this.refreshWxCode();
        // #endif
      });
    },
    updateButtonText() {
      if (this.user && this.user.id) {
        this.buttonText = '选择学校';
        return;
      }
      this.buttonText = '登录并选择学校';
    },
    goHome() {
      uni.switchTab({ url: '/pages/index/index' });
    },
    goBindSchool() {
      uni.navigateTo({ url: '/pages/plugin/index' });
    },
    goPhoneLogin() {
      uni.navigateTo({ url: '/pages/user/login' });
    },
    routeAfterLogin(userInfo) {
      this.user = userInfo || this.$db.get('user') || null;
      if (!this.user || !this.user.id) {
        this.updateButtonText();
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
        if (hasBoundSchool(this.user)) {
          this.goHome();
          return;
        }
        this.goBindSchool();
      });
    },
        refreshWxCode(done) {
      refreshWechatCode((success, code) => {
        this.code = success ? code : '';
        if (typeof done === 'function') {
          done(success);
        }
      });
    },
    fallbackToPhoneLogin(message) {
      if (message) {
        uni.showToast({
          icon: 'none',
          title: message,
          duration: 1800
        });
      }
      setTimeout(() => {
        this.goPhoneLogin();
      }, message ? 300 : 0);
    },
    handleWechatLogin() {
      if (this.wechatLoading) {
        return;
      }
      const readyState = getWechatReadyState();
      if (!readyState.ok) {
        this.fallbackToPhoneLogin(readyState.message);
        return;
      }
      if (this.code) {
        this.requestWechatProfile();
        return;
      }
      this.refreshWxCode((success) => {
        if (!success) {
          uni.showToast({
            icon: 'none',
            title: '微信登录初始化失败，请重试'
          });
          return;
        }
        this.requestWechatProfile();
      });
    },
    requestWechatProfile() {
      this.wechatLoading = true;
      requestWechatLogin(this, this.code, (result) => {
        this.wechatLoading = false;
        if (!result.ok) {
          if (result.needRefreshCode) {
            this.refreshWxCode();
          }
          if (result.message) {
            uni.showToast({
              icon: 'none',
              title: result.message
            });
          }
          return;
        }
        uni.showToast({
          title: '登录成功',
          icon: 'success'
        });
        this.routeAfterLogin(result.userinfo || null);
      });
    },
    handlePrimary() {
      if (this.user && this.user.id) {
        this.goBindSchool();
        return;
      }
      this.goPhoneLogin();
    },
  }
}
</script>

<style lang="scss">
page {
  height: 100%;
  overflow: hidden;
  background: #f7f8fc;
}
.gate-page {
  min-height: 100vh;
  background: #f7f8fc;
}
.gate-body {
  min-height: calc(100vh - 180rpx);
  padding: 80rpx 40rpx 96rpx;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
}
.gate-logo-circle {
  width: 132rpx;
  height: 132rpx;
  border-radius: 50%;
  background: #efe8ff;
  border: 2rpx solid #e0d2ff;
  display: flex;
  align-items: center;
  justify-content: center;
  box-shadow: 0 12rpx 28rpx rgba(123, 44, 255, 0.10);
  text {
    font-size: 54rpx;
    color: #7b2cff;
  }
}
.gate-title {
  margin-top: 40rpx;
  color: #1f1f1f;
  font-size: 40rpx;
  font-weight: 600;
  line-height: 1.5;
  text-align: center;
}
.gate-action {
  width: 100%;
  max-width: 560rpx;
  margin-top: 56rpx;
}
.gate-btn-primary {
  width: 100%;
  height: 92rpx;
  line-height: 92rpx;
  font-size: 30rpx;
  color: #fff;
  background: #7b2cff;
}
.gate-btn-primary[disabled] {
  opacity: 0.9;
}
</style>


