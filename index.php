<?php
/*
* ==============================================================================
* DisneyMMO EOS Homepage
*
* For use by DisneyMMO.com
* By Zach Knickerbocker
* ==============================================================================
*
* The DisneyMMO homepage in all of its glory.
* 
* Makes use of the PHPBB templating system to create the homepage and populate
* it with data.
*
* Requires the following:
*	- eos.config.php
*	- eos.functions.php
*/
define('IN_PHPBB', true);
$phpbb_root_path = (defined('PHPBB_ROOT_PATH')) ? PHPBB_ROOT_PATH : './community/';
$phpEx = substr(strrchr(__FILE__, '.'), 1);

// PHPBB includes.
include($phpbb_root_path . 'common.' . $phpEx);
include($phpbb_root_path . 'includes/functions_display.' . $phpEx);
include($phpbb_root_path . 'includes/bbcode.' . $phpEx);

// EOS includes.
include 'php/eos.config.php';
include 'php/eos.functions.php';

// Start session management.
$user->session_begin();
$auth->acl($user->data);
$user->setup();

/*
* ==============================================================================
* Showcase
* ==============================================================================
*/
$showcase_slides = constructShowcaseArray();
foreach($showcase_slides as $slide) {

	$template->assign_block_vars('showcase', array(
        'TITLE'             => $slide['title'],
		'TITLE_LINK'        => $slide['title_link'],
        'ORIGIN'            => $slide['origin'],
        'ORIGIN_LINK'	    => $slide['origin_link'],
        'CAPTION'		    => $slide['caption'],
        'CATEGORY'		    => $slide['category'],
        'BACKGROUND_URL'	=> $slide['background_url']
	));
}

/*
* ==============================================================================
* Latest Posts
* ==============================================================================
*/
$latest_posts = constructLatestPostsArray(5);
foreach($latest_posts as $post) {

	$template->assign_block_vars('latest_discussions', array(
        'LINK'       => append_sid("{$phpbb_root_path}viewtopic.$phpEx", array('f' => $post['forum_id'], 'p' => $post['last_post_id'] . "#p" . $post['last_post_id'])),
		'TITLE'      => getShortenedText(censor_text($post['title']), 25),
		'REPLIES'    => $post['replies'],
		'USERNAME'   => $post['username'],
		'AVATAR'     => $post['avatar']
	));
}

/*
* ==============================================================================
* Popular Posts
* ==============================================================================
*/
$recent_posts = constructPopularPostsArray(5);
foreach($recent_posts as $post) {

	$template->assign_block_vars('popular_discussions', array(
        'LINK'       => append_sid("{$phpbb_root_path}viewtopic.$phpEx", array('f' => $post['forum_id'], 'p' => $post['last_post_id'] . "#p" . $post['last_post_id'])),
        'TITLE'      => getShortenedText(censor_text($post['title']), 25),
		'REPLIES'    => $post['replies'],
		'USERNAME'   => $post['username'],
		'AVATAR'     => $post['avatar']
	));
}

/*
* ==============================================================================
* Articles
* ==============================================================================
*/
$begin = 0;
$end = 14;
$count = 0;

$articles = constructArticlesArray($begin, $end);
foreach($articles as $article) {
    
    // Assign block var for news feed.
    $template->assign_block_vars('articles_feed', array(
        'LINK'        => append_sid($phpbb_root_path.'viewtopic.'.$phpEx, array('f' => $article['forum_id'], 't' => $article['topic_id'])),
        'TITLE'       => getShortenedText(censor_text($article['title']), $eos_config['articles_max_title_length']),
        'PREVIEW'     => $article['preview'],
        'THUMBNAIL'   => $article['thumbnail'],
        'AUTHOR'      => $article['author'],
        'AUTHOR_LINK' => $article['author_link'],
        'TIME'        => $article['time'],
        'REPLIES'     => $article['replies'],
        'GAME'        => $article['game']
    ));
	
	$count++;
}

/*
* ==============================================================================
* Tweets
* ==============================================================================
*/
$tweets = constructTweetsArray(5);

if($tweets) {

    // Assign block var for tweet 1.
    foreach($tweets as $tweet) {

        $template->assign_block_vars('tweets', array(
            'STATUS_PLAIN'  => $tweet['status_plain'],
            'STATUS'        => $tweet['status'],
            'NAME'          => $tweet['account_name'],
            'LINK'          => $tweet['account_link'],
            'ABBR'          => $tweet['account_abbr']
        ));
    }
}

/*
* ==============================================================================
* Latest Poll
* ==============================================================================
*/

// Generate form key for poll.
add_form_key('eos-poll');

$latest_poll = constructLatestPollArray();

$template->assign_block_vars('latest_poll', array(
    'S_POLL_HAS_OPTIONS'	=> $latest_poll['metadata']['s_poll_has_options'],
    'POLL_QUESTION'			=> $latest_poll['metadata']['poll_question'],
    'POLL_AUTHOR'			=> $latest_poll['metadata']['poll_author'],
    'POLL_TIME'				=> $latest_poll['metadata']['poll_time'],
    'U_POLL_TOPIC'			=> $latest_poll['metadata']['u_poll_topic'],
    'POLL_LENGTH'			=> $latest_poll['metadata']['poll_length'],
    'TOPIC_ID'				=> $latest_poll['metadata']['topic_id'],
    'FORUM_ID'              => $latest_poll['metadata']['forum_id'],
    'CUR_VOTED_ID'          => $latest_poll['metadata']['cur_voted_id'],
    'TOTAL_VOTES'			=> $latest_poll['metadata']['total_votes'],
    'L_MAX_VOTES'			=> $latest_poll['metadata']['l_max_votes'],
    'L_POLL_LENGTH'			=> $latest_poll['metadata']['l_poll_length'],
    'HIGHEST_OPTION_PCT'    => $latest_poll['metadata']['highest_option_pct'],
    'S_CAN_VOTE'			=> $latest_poll['metadata']['s_can_vote'],
    'S_DISPLAY_RESULTS'		=> $latest_poll['metadata']['s_display_results'],
    'S_IS_MULTI_CHOICE'		=> $latest_poll['metadata']['s_is_multi_choice'],
    'S_POLL_ACTION'			=> $latest_poll['metadata']['s_poll_action'],
    'U_VIEW_RESULTS'		=> $latest_poll['metadata']['u_view_results'],
    'U_VIEW_TOPIC'			=> $latest_poll['metadata']['u_view_topic']
));

foreach($latest_poll['options'] as $option) {

    // Normalize poll percentages to 100%.
    $percent_of_max = $option['poll_option_pct'] / $latest_poll['metadata']['highest_option_pct'];
        
    $template->assign_block_vars('latest_poll.options', array(
        'POLL_OPTION_ID'		=> $option['poll_option_id'],
        'POLL_OPTION_CAPTION'	=> $option['poll_option_caption'],
        'POLL_OPTION_RESULT'	=> $option['poll_option_result'],
        'POLL_OPTION_PERCENT'	=> $option['poll_option_percent'],
        'POLL_OPTION_PCT'		=> $option['poll_option_pct'],
        'POLL_OPTION_WIDTH'     => $percent_of_max,
        'POLL_OPTION_IMG'		=> $option['poll_option_img'],
        'POLL_OPTION_VOTED'		=> $option['poll_option_voted']
    ));
    
    unset($percent_of_max);
}


/*
* ==============================================================================
* Build Page Display (Header, Body, Footer, etc.)
* ==============================================================================
*/

// Assign page specific vars.
$template->assign_vars(array(

    'DMMO_HTML_CLASS'       => 'fixed-width',
    'PAGE_ID'               => 'homepage')
);

// Output page contents.
page_header();
$template->set_filenames(array('body' => 'index_body.html'));
page_footer();

?>