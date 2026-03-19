<template>
  <view class="school-page" :style="themeVarsStyle">
    <cu-custom bgColor="bg-white" :isBack="true">
      <block slot="backText">返回</block>
      <block slot="content">选择学校</block>
    </cu-custom>

    <view class="school-page-body">
      <view class="school-location-bar">
        <view class="school-location-main">
          <text class="cuIcon-locationfill school-location-icon"></text>
          <text class="school-location-text">{{ locationText }}</text>
        </view>
        <text class="school-location-action" @tap="refreshLocation">{{ locating ? '定位中' : '重新定位' }}</text>
      </view>

      <view v-if="recommendedSchool" class="school-recommend-card" @tap="confirmBind(recommendedSchool)">
        <view class="school-recommend-label">推荐学校</view>
        <view class="school-recommend-name">{{ recommendedSchool.name }}</view>
        <view class="school-recommend-meta">{{ recommendedReason }}</view>
      </view>

      <view class="school-search-bar">
        <text class="cuIcon-search school-search-icon"></text>
        <input
          class="school-search-input"
          type="text"
          v-model="keyword"
          placeholder="输入学校名字搜索"
          confirm-type="search"
          cursor-spacing="24"
          @input="handleKeywordInput"
          @confirm="fetchSchools"
        />
      </view>

      <scroll-view class="school-scroll" scroll-y>
        <view v-if="loaded && schoolSections.length > 0" class="school-section-list">
          <view class="school-section" v-for="section in schoolSections" :key="section.letter">
            <view class="school-section-letter">{{ section.letter }}</view>
            <view
              class="school-row"
              v-for="item in section.items"
              :key="item.id"
              @tap="confirmBind(item)"
            >
              <text class="school-row-name">{{ item.name }}</text>
            </view>
          </view>
        </view>

        <view class="school-empty" v-if="loaded && schoolSections.length === 0">
          暂无匹配学校
        </view>
      </scroll-view>
    </view>
  </view>
</template>

<script>
const INITIAL_BOUNDARIES = [
  { letter: 'A', start: '阿' },
  { letter: 'B', start: '芭' },
  { letter: 'C', start: '擦' },
  { letter: 'D', start: '搭' },
  { letter: 'E', start: '蛾' },
  { letter: 'F', start: '发' },
  { letter: 'G', start: '噶' },
  { letter: 'H', start: '哈' },
  { letter: 'J', start: '击' },
  { letter: 'K', start: '喀' },
  { letter: 'L', start: '垃' },
  { letter: 'M', start: '妈' },
  { letter: 'N', start: '拿' },
  { letter: 'O', start: '哦' },
  { letter: 'P', start: '啪' },
  { letter: 'Q', start: '期' },
  { letter: 'R', start: '然' },
  { letter: 'S', start: '撒' },
  { letter: 'T', start: '塌' },
  { letter: 'W', start: '挖' },
  { letter: 'X', start: '昔' },
  { letter: 'Y', start: '压' },
  { letter: 'Z', start: '匝' }
];

export default {
  data() {
    return {
      keyword: '',
      schoolList: [],
      nearbySchoolList: [],
      loaded: false,
      user: null,
      searchTimer: null,
      currentLatitude: 0,
      currentLongitude: 0,
      currentCity: '',
      currentDistrict: '',
      currentAddress: '',
      locating: false,
      locationFailed: false
    };
  },
  computed: {
    locationText() {
      if (this.locating) {
        return '正在获取当前位置...';
      }
      if (this.currentCity) {
        return this.currentDistrict ? (this.currentCity + ' · ' + this.currentDistrict) : this.currentCity;
      }
      if (this.locationFailed) {
        return '定位失败，请手动搜索学校';
      }
      return '未获取到定位，请手动搜索学校';
    },
    recommendedSchool() {
      if (!this.nearbySchoolList.length) {
        return null;
      }
      return this.nearbySchoolList[0];
    },
    recommendedReason() {
      if (!this.recommendedSchool) {
        return '';
      }
      if (this.recommendedSchool.distanceText) {
        return '距你约 ' + this.recommendedSchool.distanceText;
      }
      if (this.currentCity) {
        return '根据你当前定位的 ' + this.currentCity + ' 为你推荐';
      }
      return '为你推荐';
    },
    schoolSections() {
      const groups = {};
      const list = this.schoolList.slice().sort((left, right) => this.compareSchoolName(left.name, right.name));

      list.forEach(item => {
        const letter = this.getSchoolInitial(item.name);
        if (!groups[letter]) {
          groups[letter] = [];
        }
        groups[letter].push(item);
      });

      return Object.keys(groups)
        .sort((left, right) => {
          if (left === '#') {
            return 1;
          }
          if (right === '#') {
            return -1;
          }
          return left.localeCompare(right);
        })
        .map(letter => ({
          letter,
          items: groups[letter]
        }));
    }
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
            uni.reLaunch({ url: '/pages/index/index' });
          }, 400);
          return;
        }
      this.fetchSchools();
      this.refreshLocation();
    });
  },
  onUnload() {
    this.clearSearchTimer();
  },
  methods: {
    fetchSchools() {
      this.$api.schoolListData(
        {
          keyword: this.keyword,
          limit: 50
        },
        res => {
          this.loaded = true;
          if (res.code === 1) {
            this.schoolList = (res.data.list || []).map(item => this.decorateSchool(item));
          } else {
            this.schoolList = [];
            this.$common.normalToShow(res.msg || '获取学校失败');
          }
        }
      );
    },
    fetchNearbySchools() {
      if (!this.currentCity) {
        this.nearbySchoolList = [];
        return;
      }
      this.$api.schoolListData(
        {
          city: this.currentCity,
          latitude: this.currentLatitude,
          longitude: this.currentLongitude,
          limit: 20
        },
        res => {
          if (res.code === 1) {
            this.nearbySchoolList = (res.data.list || [])
              .map(item => this.decorateSchool(item))
              .slice()
              .sort((left, right) => {
                if (left.distanceSort !== right.distanceSort) {
                  return left.distanceSort - right.distanceSort;
                }
                return this.compareSchoolName(left.name, right.name);
              });
          } else {
            this.nearbySchoolList = [];
          }
        }
      );
    },
    handleKeywordInput() {
      this.clearSearchTimer();
      this.searchTimer = setTimeout(() => {
        this.fetchSchools();
      }, 250);
    },
    clearSearchTimer() {
      if (this.searchTimer) {
        clearTimeout(this.searchTimer);
        this.searchTimer = null;
      }
    },
    compareSchoolName(leftName, rightName) {
      const left = leftName || '';
      const right = rightName || '';
      try {
        return left.localeCompare(right, 'zh-Hans-CN-u-co-pinyin');
      } catch (error) {
        return left.localeCompare(right);
      }
    },
    decorateSchool(item) {
      const school = Object.assign({}, item || {});
      const distanceMeter = Number(school.distance_meter || 0);
      school.distance_meter = distanceMeter > 0 ? distanceMeter : 0;
      school.distanceSort = school.distance_meter > 0 ? school.distance_meter : Number.MAX_SAFE_INTEGER;
      school.distanceText = this.formatDistance(school.distance_meter);
      return school;
    },
    formatDistance(distanceMeter) {
      const value = Number(distanceMeter || 0);
      if (!(value > 0)) {
        return '';
      }
      if (value >= 1000) {
        return (value / 1000).toFixed(value >= 10000 ? 0 : 1) + 'km';
      }
      return Math.round(value) + 'm';
    },
    getSchoolInitial(name) {
      const value = (name || '').trim();
      if (!value) {
        return '#';
      }
      const firstChar = value.charAt(0);
      const upperChar = firstChar.toUpperCase();

      if (/^[A-Z]$/.test(upperChar)) {
        return upperChar;
      }
      if (/^[0-9]$/.test(firstChar)) {
        return '#';
      }

      for (let index = 0; index < INITIAL_BOUNDARIES.length - 1; index++) {
        const current = INITIAL_BOUNDARIES[index];
        const next = INITIAL_BOUNDARIES[index + 1];
        if (firstChar.localeCompare(current.start) >= 0 && firstChar.localeCompare(next.start) < 0) {
          return current.letter;
        }
      }

      if (firstChar.localeCompare(INITIAL_BOUNDARIES[INITIAL_BOUNDARIES.length - 1].start) >= 0) {
        return 'Z';
      }
      return '#';
    },
    refreshLocation() {
      if (this.locating) {
        return;
      }
      this.locating = true;
      this.locationFailed = false;
      uni.getLocation({
        type: 'gcj02',
        success: res => {
          this.currentLatitude = Number(res.latitude || 0);
          this.currentLongitude = Number(res.longitude || 0);
          const latlng = res.latitude + ',' + res.longitude;
          this.$api.locationAddress({ latlng }, data => {
            this.locating = false;
            if (data.code === 1 && data.data) {
              this.currentCity = data.data.city || '';
              this.currentDistrict = data.data.district || '';
              this.currentAddress = data.data.address || '';
              this.fetchNearbySchools();
              return;
            }
            this.locationFailed = true;
            this.currentLatitude = 0;
            this.currentLongitude = 0;
            this.currentCity = '';
            this.currentDistrict = '';
            this.currentAddress = '';
            this.nearbySchoolList = [];
          });
        },
        fail: () => {
          this.locating = false;
          this.locationFailed = true;
          this.currentLatitude = 0;
          this.currentLongitude = 0;
          this.currentCity = '';
          this.currentDistrict = '';
          this.currentAddress = '';
          this.nearbySchoolList = [];
        }
      });
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
          setTimeout(() => {
            uni.reLaunch({ url: '/pages/index/index' });
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
.school-page {
  min-height: 100vh;
  background: #ffffff;
}
.school-page-body {
  height: calc(100vh - 180rpx);
  padding: 18rpx 0 0;
  display: flex;
  flex-direction: column;
}
.school-location-bar {
  height: 44rpx;
  margin: 0 24rpx 18rpx;
  display: flex;
  align-items: center;
  justify-content: space-between;
}
.school-location-main {
  display: flex;
  align-items: center;
  min-width: 0;
}
.school-location-icon {
  margin-right: 10rpx;
  color: #6f39f6;
  font-size: 28rpx;
}
.school-location-text {
  color: #4b5563;
  font-size: 24rpx;
  line-height: 1.4;
}
.school-location-action {
  flex-shrink: 0;
  color: #6f39f6;
  font-size: 24rpx;
}
.school-recommend-card {
  margin: 0 24rpx 20rpx;
  padding: 24rpx 26rpx;
  border-radius: 20rpx;
  background: linear-gradient(135deg, #f7f3ff 0%, #f4f7ff 100%);
}
.school-recommend-label {
  color: #6f39f6;
  font-size: 22rpx;
  line-height: 1.4;
}
.school-recommend-name {
  margin-top: 10rpx;
  color: #111827;
  font-size: 32rpx;
  font-weight: 600;
  line-height: 1.4;
}
.school-recommend-meta {
  margin-top: 8rpx;
  color: #6b7280;
  font-size: 24rpx;
  line-height: 1.5;
}
.school-search-bar {
  display: flex;
  align-items: center;
  height: 84rpx;
  margin: 0 24rpx;
  padding: 0 26rpx;
  background: #f7f8fb;
  border-radius: 42rpx;
}
.school-search-icon {
  margin-right: 16rpx;
  color: #303133;
  font-size: 36rpx;
}
.school-search-input {
  flex: 1;
  height: 84rpx;
  font-size: 28rpx;
  color: #202124;
}
.school-scroll {
  flex: 1;
  margin-top: 20rpx;
}
.school-section-letter {
  position: sticky;
  top: 0;
  z-index: 2;
  height: 62rpx;
  padding: 0 24rpx;
  line-height: 62rpx;
  background: #f5f5f5;
  color: #7b7f86;
  font-size: 28rpx;
}
.school-row {
  min-height: 92rpx;
  margin-left: 24rpx;
  padding: 0 32rpx 0 4rpx;
  display: flex;
  align-items: center;
  background: #ffffff;
  border-bottom: 1rpx solid #f3f3f3;
}
.school-row-name {
  color: #1a1a1a;
  font-size: 28rpx;
  line-height: 1.5;
}
.school-empty {
  padding: 120rpx 24rpx;
  color: #9ca3af;
  font-size: 28rpx;
  text-align: center;
}
</style>
