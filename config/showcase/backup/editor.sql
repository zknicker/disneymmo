DROP TABLE IF EXISTS phpbb_showcase;
CREATE TABLE `phpbb_showcase` (
  `slot` tinyint unsigned NOT NULL DEFAULT 0,
  `topic_id` mediumint(8) unsigned NOT NULL DEFAULT 0,
  `title` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `caption` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `preview` varchar(1000) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `category` varchar(8) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `cover` varchar(2000) NOT NULL DEFAULT ''
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

INSERT INTO phpbb_showcase VALUES (1, 1002, 'Status Report #11', 'Temp Caption', 'The next status report is in! Click through to find out what is going on with DisneyMMO development.', 'dmmo', 'http://disneymmo.com/images/news/default/dmmo/generic.jpg'),(2, 1002, 'Status Report #10', 'Temp Caption', 'The next status report is in! Click through to find out what is going on with DisneyMMO development.', 'dmmo', 'http://disneymmo.com/images/news/default/dmmo/generic.jpg'),(3, 1002, 'Status Report #9', 'Temp Caption', 'The next status report is in! Click through to find out what is going on with DisneyMMO development.', 'dmmo', 'http://disneymmo.com/images/news/default/dmmo/generic.jpg'),(4, 1002, 'Status Report #8', 'Temp Caption', 'The next status report is in! Click through to find out what is going on with DisneyMMO development.', 'dmmo', 'http://disneymmo.com/images/news/default/dmmo/generic.jpg'),(4, 1002, 'Status Report #8', 'Temp Caption', 'The next status report is in! Click through to find out what is going on with DisneyMMO development.', 'dmmo', 'http://disneymmo.com/images/news/default/dmmo/generic.jpg');