<?php
// Define PHPBB.
define('IN_PHPBB', true);
$phpbb_root_path = '../../community/';
$phpEx = substr(strrchr(__FILE__, '.'), 1);

// PHPBB includes.
include($phpbb_root_path . 'common.' . $phpEx);
include($phpbb_root_path . 'includes/functions_display.' . $phpEx);
include($phpbb_root_path . 'includes/bbcode.' . $phpEx);

// DisneyMMO includes.
include("../../php/eos.functions.php");

// Start session management.
$user->session_begin();
$auth->acl($user->data);
$user->setup();

// Variables.
$is_admin = false; // Admins only!

// Admin check.
if ($user->data['is_registered'] && $auth->acl_get('a_')) {

    $is_admin = true;
}

// Main logic.
if(isset($_POST['topic_id'])) {

    $topic = array(); /* post requested via topic id */
    $bbcode_bitfield = ''; /* bbcode bitfield, populated later */

    // Construct the query.
    $thread_query = 

        "SELECT t.forum_id, t.topic_title, t.topic_first_post_id, p.topic_id, p.post_id, p.post_text, p.bbcode_bitfield, p.bbcode_uid
         FROM " . TOPICS_TABLE . " t, " . POSTS_TABLE . " p 
         WHERE t.topic_id = " . $_POST['topic_id'] . "
            AND t.topic_id = p.topic_id
            AND p.post_id = t.topic_first_post_id";

    // Retrieve query result, and store in array.
    $result = $db->sql_query($thread_query);
    $r = $db->sql_fetchrow($result);

    $topic = array(
        'topic_id'			=>	$r['topic_id'],
        'topic_time'		=>	$r['topic_time'],
        'topic_title'		=>	$r['topic_title'],
        'post_text'			=> 	$r['post_text'],
        'bbcode_uid'		=> 	$r['bbcode_uid'],
        'bbcode_bitfield'	=> 	$r['bbcode_bitfield'],
        'topic_replies' 	=>	$r['topic_replies'],
        'forum_id' 			=> 	$r['forum_id']
    );

    $bbcode_bitfield = $bbcode_bitfield | base64_decode($r['bbcode_bitfield']);

    // Instantiate BBCode if it does not already exist.
    if ($bbcode_bitfield !== '')
    {
       $bbcode = new bbcode(base64_encode($bbcode_bitfield));
    }

    // Attempt to get preview from BBCode.
    $preview = getPreviewTextFromPost($topic['post_text']);
    $preview = str_replace("\n", '<br />', $preview);
    $preview = smiley_text($preview);
    $preview = decodeText($preview);

    // Convert preview from BBCode to HTML.
    if($topic['bbcode_bitfield'])
    {
      $bbcode->bbcode_second_pass($preview, $topic['bbcode_uid'], $topic['bbcode_bitfield']);
    }
    
    // Get category from forum ID.
    $meta = getForumMeta($topic['forum_id']);
    $category = $meta['abbr'];
    
    // Decode HTML entities in title.
    $title = decodeText($topic['topic_title']);
    
    // Atempt to get cover from BBCode.
    $cover = getCoverImageFromPost($topic['post_text']);
    
    // If cover could not be retrieved, get a stock image.
    if(empty($cover)) {
    
        $cover = getCoverImageFromStock($category);
    }

    echo json_encode(array(
        
        'topic_id'  =>  $topic['topic_id'],
        'title'     =>  $title,
        'preview'   =>  $preview,
        'category'  =>  $category,
        'cover'     =>  decodeText($cover),
            
    ));

} else {

    // Exit with error.
    header('HTTP/1.1 500 Internal Server Booboo');
    header('Content-Type: application/json');
    die('ERROR');

}

?>