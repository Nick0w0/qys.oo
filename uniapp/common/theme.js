const THEME_STORAGE_KEY = 'schoolTheme';

const DEFAULT_THEME = Object.freeze({
	primary: '#8FBFF6',
	secondary: '#C9E0FF',
	textColor: '#111827',
	logo: '',
	headerBgImage: ''
});

function isPlainObject(value) {
	return Object.prototype.toString.call(value) === '[object Object]';
}

function normalizeColor(value, fallback) {
	if (typeof value !== 'string') {
		return fallback;
	}
	const color = value.trim();
	return /^#([0-9a-fA-F]{3}|[0-9a-fA-F]{6})$/.test(color) ? color : fallback;
}

function mergeTheme(theme) {
	const source = isPlainObject(theme) ? theme : {};
	return {
		primary: normalizeColor(source.primary || source.theme_primary, DEFAULT_THEME.primary),
		secondary: normalizeColor(source.secondary || source.theme_secondary, DEFAULT_THEME.secondary),
		textColor: normalizeColor(source.textColor || source.theme_text_color, DEFAULT_THEME.textColor),
		logo: typeof source.logo === 'string' ? source.logo : '',
		headerBgImage: typeof source.headerBgImage === 'string'
			? source.headerBgImage
			: (typeof source.header_bg_image === 'string' ? source.header_bg_image : '')
	};
}

function getThemeByUser(user) {
	const schoolInfo = user && user.school_info ? user.school_info : {};
	return mergeTheme(schoolInfo);
}

function readTheme() {
	try {
		return mergeTheme(uni.getStorageSync(THEME_STORAGE_KEY));
	} catch (error) {
		return mergeTheme();
	}
}

function writeTheme(theme) {
	const nextTheme = mergeTheme(theme);
	try {
		uni.setStorageSync(THEME_STORAGE_KEY, nextTheme);
	} catch (error) {}
	return nextTheme;
}

function syncThemeFromUser(user) {
	return writeTheme(getThemeByUser(user));
}

function buildThemeVars(theme) {
	const currentTheme = mergeTheme(theme);
	return {
		'--school-theme-primary': currentTheme.primary,
		'--school-theme-secondary': currentTheme.secondary,
		'--school-theme-text': currentTheme.textColor
	};
}

function buildThemeGradient(theme, angle = '135deg') {
	const currentTheme = mergeTheme(theme);
	return `linear-gradient(${angle}, ${currentTheme.primary} 0%, ${currentTheme.secondary} 100%)`;
}

export {
	THEME_STORAGE_KEY,
	DEFAULT_THEME,
	mergeTheme,
	getThemeByUser,
	readTheme,
	writeTheme,
	syncThemeFromUser,
	buildThemeVars,
	buildThemeGradient
};
