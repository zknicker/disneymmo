DROP TABLE IF EXISTS phpbb_articles;
CREATE TABLE `phpbb_articles` (
  `topic_id` mediumint(8) unsigned NOT NULL DEFAULT 0,
  `preview` varchar(2500) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `thumbnail` varchar(1000) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT ''
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;