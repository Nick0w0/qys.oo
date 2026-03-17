function getMiniProgramAppId() {
  let appId = '';
  // #ifdef MP-WEIXIN
  try {
    const accountInfo = wx.getAccountInfoSync ? wx.getAccountInfoSync() : null;
    appId = accountInfo && accountInfo.miniProgram ? accountInfo.miniProgram.appId : '';
  } catch (error) {}
  // #endif
  return appId;
}

export function hasBoundSchool(userInfo) {
  return !!(userInfo && userInfo.school_id);
}

export function getWechatReadyState() {
  // #ifdef MP-WEIXIN
  if (typeof wx === 'undefined' || typeof uni.getUserProfile !== 'function') {
    return {
      ok: false,
      message: '当前环境暂不支持微信快捷登录'
    };
  }
  const appId = getMiniProgramAppId();
  if (!appId) {
    return {
      ok: false,
      message: '当前小程序未配置 AppID，先使用手机号登录'
    };
  }
  return {
    ok: true,
    appId: appId
  };
  // #endif
  return {
    ok: false,
    message: '当前环境暂不支持微信快捷登录'
  };
}

export function refreshWechatCode(callback = function() {}) {
  // #ifdef MP-WEIXIN
  if (typeof wx === 'undefined' || typeof wx.login !== 'function') {
    callback(false, '');
    return;
  }
  wx.login({
    success: (res) => {
      const code = res && res.code ? res.code : '';
      callback(!!code, code);
    },
    fail: () => {
      callback(false, '');
    }
  });
  return;
  // #endif
  callback(false, '');
}

export function persistThirdLogin(vm, result) {
  if (!vm || !vm.$db || !result) {
    return null;
  }
  const auth = result.auth || {};
  const userinfo = result.userinfo || {};
  try {
    vm.$db.set('upload', 1);
    vm.$db.set('login', 1);
    vm.$db.set('auth', auth);
    vm.$db.set('user', userinfo);
    if (userinfo.token) {
      vm.$db.set('token', userinfo.token);
    }
  } catch (error) {}
  return userinfo;
}

export function requestWechatLogin(vm, code, callback = function() {}) {
  if (!vm || !vm.$api || !vm.$api.third) {
    callback({
      ok: false,
      message: '微信登录服务不可用'
    });
    return;
  }
  if (!code) {
    callback({
      ok: false,
      needRefreshCode: true,
      message: '微信登录初始化失败，请重试'
    });
    return;
  }
  uni.getUserProfile({
    desc: '用于完善会员资料',
    success: (profileRes) => {
      vm.$api.third(
        {
          code: code,
          platform: 'wechat',
          encrypted_data: profileRes.encryptedData,
          iv: profileRes.iv,
          raw_data: profileRes.rawData,
          signature: profileRes.signature
        },
        (data) => {
          if (data.code !== 1 || !data.data) {
            callback({
              ok: false,
              needRefreshCode: true,
              message: data.msg || '微信登录失败',
              response: data
            });
            return;
          }
          const userinfo = persistThirdLogin(vm, data.data);
          callback({
            ok: true,
            userinfo: userinfo,
            response: data
          });
        }
      );
    },
    fail: (error) => {
      const errMsg = error && error.errMsg ? error.errMsg : '';
      callback({
        ok: false,
        canceled: errMsg.indexOf('auth deny') >= 0 || errMsg.indexOf('user deny') >= 0,
        needRefreshCode: true,
        message: errMsg.indexOf('auth deny') >= 0 || errMsg.indexOf('user deny') >= 0 ? '你已取消微信授权' : '微信授权失败，请重试',
        error: error
      });
    }
  });
}
