import {
	DEFAULT_THEME,
	buildThemeGradient,
	buildThemeVars,
	readTheme,
	syncThemeFromUser,
	writeTheme
} from './theme.js';

export default {
	data() {
		return {
			_schoolThemeState: readTheme()
		};
	},
	onShow() {
		this.restoreStoredAppTheme();
	},
	computed: {
		appTheme() {
			return this._schoolThemeState || readTheme();
		},
		themeVarsStyle() {
			return buildThemeVars(this.appTheme);
		},
		themePrimary() {
			return this.appTheme.primary;
		},
		themeSecondary() {
			return this.appTheme.secondary;
		},
		themeTextColor() {
			return this.appTheme.textColor;
		},
		themeGradientStyle() {
			return {
				...this.themeVarsStyle,
				backgroundColor: this.themePrimary,
				backgroundImage: buildThemeGradient(this.appTheme)
			};
		}
	},
	methods: {
		refreshAppTheme(user) {
			const nextTheme = user ? syncThemeFromUser(user) : writeTheme(DEFAULT_THEME);
			this._schoolThemeState = nextTheme;
			return nextTheme;
		},
		restoreStoredAppTheme() {
			const currentTheme = readTheme();
			this._schoolThemeState = currentTheme;
			return currentTheme;
		},
		setAppTheme(theme) {
			const nextTheme = writeTheme(theme);
			this._schoolThemeState = nextTheme;
			return nextTheme;
		},
		resetAppTheme() {
			const nextTheme = writeTheme(DEFAULT_THEME);
			this._schoolThemeState = nextTheme;
			return nextTheme;
		}
	}
};
