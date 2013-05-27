<?php
/*
* ==============================================================================
* DisneyMMO EOS Latest Poll AJAX
*
* For use by DisneyMMO.com
* By Zach Knickerbocker
* ==============================================================================
*
* Registers a user's vote to a given poll and returns updated information.
*/

// Define PHPBB.
define('IN_PHPBB', true);
$phpbb_root_path = '../community/';
$phpEx = substr(strrchr(__FILE__, '.'), 1);

// PHPBB includes.
include($phpbb_root_path . 'common.' . $phpEx);
include($phpbb_root_path . 'includes/functions_display.' . $phpEx);
include($phpbb_root_path . 'includes/bbcode.' . $phpEx);

// DisneyMMO Includes.
include('eos.config.php');
include('eos.functions.php');

// Start session management.
$user->session_begin();
$auth->acl($user->data);
$user->setup();

$begin = request_var('begin', 0);   // first article to retrieve (sql limit)
$qty = request_var('qty', 10);      // last article to retrieve (sql limit)
$count = 0;                         // articles retrieved so far

// Gather the articles and throw them at the template.
$articles = constructArticlesArray($begin, $qty);
foreach($articles as $article) {

	// Determine article background type.
         if($count % 2 == 0)	{	$decoration = 'light1';	}
    else if($count % 2 == 1)	{	$decoration = 'dark1';	}
    
    // Assign block var for news feed.
    $template->assign_block_vars('articles_feed', array(
        'LINK'       => append_sid($phpbb_root_path.'viewtopic.'.$phpEx, array('f' => $article['forum_id'], 't' => $article['topic_id'])),
        'TITLE'      => getShortenedText(censor_text($article['title']), $eos_config['max_title_length']),
        'PREVIEW'    => getShortenedText(censor_text($article['preview']), $eos_config['max_preview_length']),
        'TIME'       => $article['time'],
        'REPLIES'    => $article['replies'],
        'DECORATION' => $decoration,
        'GAME'       => $article['game']
    ));
	
	$count++;
}

// Output article HTML.
page_header();
$template->set_filenames(array('body' => 'index_article.html'));
page_footer();

?>