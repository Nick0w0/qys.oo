<template>
  <view class="gate-page" :style="themeVarsStyle">
    <image class="gate-cover-image" src="../../static/images/login-cover.png" mode="aspectFill"></image>
    <view class="gate-cover-mask"></view>
    <view class="gate-body">
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
      buttonText: '手机号登录',
      code: '',
      wechatLoading: false
    }
  },
  computed: {
    isLoggedIn() {
      return !!(this.user && this.user.id);
    }
  },
  onShow() {
    this.user = this.$db.get('user') || null;
    if (this.isLoggedIn) {
      this.syncUserState();
      return;
    }
    this.updateButtonText();
    // #ifdef MP-WEIXIN
    this.refreshWxCode();
    // #endif
  },
  methods: {
    getPostLoginUrl(userInfo) {
      return hasBoundSchool(userInfo) ? '/pages/index/index' : '/pages/plugin/index';
    },
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
        if (this.user && this.user.id) {
          this.goBindSchool();
          return;
        }
        this.updateButtonText();
        // #ifdef MP-WEIXIN
        this.refreshWxCode();
        // #endif
      });
    },
    updateButtonText() {
      if (this.isLoggedIn) {
        this.buttonText = '选择学校';
        return;
      }
      this.buttonText = '手机号登录';
    },
    goHome() {
      uni.reLaunch({ url: '/pages/index/index' });
    },
    goBack() {
      const pages = getCurrentPages();
      if (pages.length > 1) {
        uni.navigateBack({ delta: 1 });
        return;
      }
      uni.reLaunch({ url: '/pages/index/index' });
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
      this.refreshAppTheme(this.user);
      const targetUrl = this.getPostLoginUrl(this.user);
      uni.reLaunch({ url: targetUrl });
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
      this.refreshWxCode(success => {
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
      requestWechatLogin(this, this.code, result => {
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
      if (this.isLoggedIn) {
        this.goBindSchool();
        return;
      }
      this.goPhoneLogin();
    }
  }
}
</script>

<style lang="scss">
page {
  min-height: 100%;
  background: #78cbc9;
}
.gate-page {
  min-height: 100vh;
  position: relative;
  overflow: hidden;
}
.gate-cover-image,
.gate-cover-mask {
  position: absolute;
  left: 0;
  top: 0;
  right: 0;
  bottom: 0;
}
.gate-cover-image {
  width: 100%;
  height: 100%;
}
.gate-cover-mask {
  background: linear-gradient(180deg, rgba(255,255,255,0.12) 0%, rgba(255,248,235,0.24) 48%, rgba(120,203,201,0.22) 100%);
}
.gate-body {
  min-height: 100vh;
  padding: 24rpx 40rpx 96rpx;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: flex-end;
  box-sizing: border-box;
  position: relative;
  z-index: 2;
}
.gate-action {
  width: 100%;
  max-width: 500rpx;
  margin-bottom: 92rpx;
}
.gate-btn-primary {
  width: 100%;
  height: 88rpx;
  line-height: 88rpx;
  font-size: 28rpx;
  font-weight: 500;
  letter-spacing: 2rpx;
  color: #fff;
  background: linear-gradient(135deg, rgba(122, 79, 255, 0.9) 0%, rgba(79, 114, 255, 0.88) 100%);
  border: 1rpx solid rgba(255,255,255,0.32);
  box-shadow: 0 18rpx 38rpx rgba(87, 103, 208, 0.22);
  backdrop-filter: blur(8rpx);
  -webkit-backdrop-filter: blur(8rpx);
}
.gate-btn-primary::after {
  border: none;
}
</style>
