-- 2026-03-18 学校主题演示数据

UPDATE `fa_school`
SET
  `logo` = '/assets/demo/schools/gj_logo.svg',
  `header_bg_image` = '/assets/demo/schools/gj_header.svg',
  `theme_primary` = '#8FAEFF',
  `theme_secondary` = '#DCE7FF',
  `theme_text_color` = '#111827'
WHERE `id` = 1;

UPDATE `fa_school`
SET
  `logo` = '/assets/demo/schools/gz_logo.svg',
  `header_bg_image` = '/assets/demo/schools/gz_header.svg',
  `theme_primary` = '#8FBFF6',
  `theme_secondary` = '#DCEBFF',
  `theme_text_color` = '#111827'
WHERE `id` = 2;

UPDATE `fa_school`
SET
  `logo` = '/assets/demo/schools/fs_logo.svg',
  `header_bg_image` = '/assets/demo/schools/fs_header.svg',
  `theme_primary` = '#7ED6B8',
  `theme_secondary` = '#D9F5EA',
  `theme_text_color` = '#102A22'
WHERE `id` = 3;
