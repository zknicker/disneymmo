<?php
/*
* ==============================================================================
* DisneyMMO EOS Configuration
*
* For use by DisneyMMO.com
* By Zach Knickerbocker
* ==============================================================================
*
* Configuration information for the DisneyMMO EOS homepage and custom
* environment.
*
* Variables in this file are referenced one or more times throughout custom
* code inserted into PHPBB, or resulting from modifications therein. These
* variables exist to provide a single access point to changing settings for
* various aspects of the website design, some of which will change significantly
* less often than others, if at all.
* 
* All relative paths within are intended to be referenced from "server_root".
* That is, the most root level of the served content.
*/

$server_root = '/home/xiris/public_html';
$server_name = (!empty($_SERVER['HTTP_HOST'])) ? strtolower($_SERVER['HTTP_HOST']) : ((!empty($_SERVER['SERVER_NAME'])) ? $_SERVER['SERVER_NAME'] : getenv('SERVER_NAME'));
$eos_config = array(

    "website_name"                  =>  'disneymmo',

    /* dirs */
    "public_root"                   =>  'http://' + $server_name + '/',
    "server_root"                   =>  $server_root,
    "previews_dir"                  =>  $server_root . '/images/previews/',
    "profile_customizations_dir"    =>  $server_root . '/images/profiles/customizations',

    /* tweets */
    "tweets_accounts"               =>  array('disney', 'Disney_Toontown', 'pirates_online', 'clubpenguin', 'DisneyMMO'),
    "tweets_per_account"            =>  2,
    "tweets_json_path"              =>  'json/tweets.json',
    
	/* poll */
	"poll_source_forums"            =>  array(36),
	
	/* showcase */
    "showcase_table"                =>  'phpbb_showcase',
    "showcase_json_path"            =>  'json/showcase.json',
    "default_slide_bg"              =>  'images/news/default/dmmo/generic.jpg',
    "uploaded_slide_bg_path"        =>  'images/showcase/',
	
    /* articles stream */
    "articles_table"                =>  'phpbb_articles',
    "articles_thumbnails_dir"       =>  'images/articles/',
    "articles_def_thumbnails_dir"   =>  'images/articles/default/',
    "articles_def_thumbnail"        =>  'images/articles/default/dmmo/dmmo-1.jpg',
    "articles_source_forums"        =>  array(2, 32, 33, 34, 35),
    "articles_mmo_source_forums"    =>  array(32, 33, 34, 35),
    "articles_max_title_length"     =>  42,
    "articles_max_preview_length"   =>  380,
    
    /* forums */
    "filters_table"                 =>  'phpbb_discussion_filters',
    "filter_icon_path"              =>  'images/layout/filters/',
    "default_forum_abbr"            =>  'dmmo',
    
    /* messages */
    "messages_table"                =>  'phpbb_messages',
    "messages_table_track"          =>  'phpbb_messages_track',
    "messages_table_watching"       =>  'phpbb_messages_watching',
    
    /* profiles */
    "profile_customizations_table"  =>  'phpbb_profile_customizations',
    "profile_customizations_path"   =>  'images/profiles/customizations/',
    
    /* uploads */
    "upload_max_image_length"       =>  1048576,
);