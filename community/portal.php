<?php
/**
*
* @package phpBB3
* @version $Id$
* @copyright (c) 2005 phpBB Group
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

/**
*/

/**
* @ignore
*/
define('IN_PHPBB', true);
$phpbb_root_path = (defined('PHPBB_ROOT_PATH')) ? PHPBB_ROOT_PATH : './';
$phpEx = substr(strrchr(__FILE__, '.'), 1);
include($phpbb_root_path . 'common.' . $phpEx);
include($phpbb_root_path . 'includes/functions_display.' . $phpEx);

// DisneyMMO EOS Includes
include($phpbb_root_path . '../php/eos.functions.' . $phpEx);
include($phpbb_root_path . '../php/eos.config.' . $phpEx);

// Start session management
$user->session_begin();
$auth->acl($user->data);
$user->setup('viewforum');

// Get community statistics
$total_posts	= $config['num_posts'];
$total_topics	= $config['num_topics'];
$total_users	= $config['num_users'];


/*
* ==============================================================================
* Generate birthday list.
* ==============================================================================
* 
* Generate the birthday list (if permissibile), and store it.
*/
$birthday_list = '';
if ($config['load_birthdays'] && $config['allow_birthdays'] && $auth->acl_gets('u_viewprofile', 'a_user', 'a_useradd', 'a_userdel'))
{
	$now = phpbb_gmgetdate(time() + $user->timezone + $user->dst);

	// Display birthdays of 29th february on 28th february in non-leap-years
	$leap_year_birthdays = '';
	if ($now['mday'] == 28 && $now['mon'] == 2 && !$user->format_date(time(), 'L'))
	{
		$leap_year_birthdays = " OR user_birthday LIKE '" . $db->sql_escape(sprintf('%2d-%2d-', 29, 2)) . "%'";
	}

	$sql = 'SELECT u.user_id, u.username, u.user_colour, u.user_birthday
		FROM ' . USERS_TABLE . ' u
		LEFT JOIN ' . BANLIST_TABLE . " b ON (u.user_id = b.ban_userid)
		WHERE (b.ban_id IS NULL
			OR b.ban_exclude = 1)
			AND (u.user_birthday LIKE '" . $db->sql_escape(sprintf('%2d-%2d-', $now['mday'], $now['mon'])) . "%' $leap_year_birthdays)
			AND u.user_type IN (" . USER_NORMAL . ', ' . USER_FOUNDER . ')';
	$result = $db->sql_query($sql);

	while ($row = $db->sql_fetchrow($result))
	{
		$birthday_list .= (($birthday_list != '') ? ', ' : '') . get_username_string('full', $row['user_id'], $row['username'], $row['user_colour']);

		if ($age = (int) substr($row['user_birthday'], -4))
		{
			$birthday_list .= ' (' . max(0, $now['year'] - $age) . ')';
		}
	}
	$db->sql_freeresult($result);
}

/*
* ==============================================================================
* Construct active topics stream.
* ==============================================================================
* 
* Retrieve the active topics from the database and send them to the template.
*/

// Retrieve and decode active_topics JSON file for use.
$topics_json = file_get_contents($phpbb_root_path . '../json/active_topics.json');
$topics_json = json_decode($topics_json);

// Retrieve data representation of discussion filters by forum id.
$sql = 'SELECT * FROM ' . phpbb_discussion_filters . '';
$result = $db->sql_query($sql);
$discussion_filters = array();

while($row = $db->sql_fetchrow($result)) {

    $i = $row['forum_id'];
    $discussion_filters[$i] = array(
    
        'abbr'      => $row['abbr'],
        'category'  => $row['category'],
        'icon_path' => $phpbb_root_path . '../' . $eos_config['filter_icon_path'] . $row['icon']
    );
}

// Populate template block for topics display.
foreach($topics_json as $topic) {

    $template->assign_block_vars('active_topics', array(
        'TOPIC_TITLE'           => $topic->TOPIC_TITLE,
        'TOPIC_ID'              => $topic->TOPIC_ID,
        'TOPIC_LINK'            => $topic->U_VIEW_TOPIC . '&style=' . $user->theme['style_id'],
        'TOPIC_LAST_REPLY_LINK' => $topic->U_LAST_POST,
        'FILTER_NAME'           => $topic->FORUM_TITLE,
        'FILTER_NAME_CLEAN'     => strtolower(str_replace(" ", "-", $topic->FORUM_TITLE)),
        'FILTER_ICON'           => $discussion_filters[$topic->FORUM_ID]['icon_path'],
        'CATEGORY'              => $discussion_filters[$topic->FORUM_ID]['category'],
        'CATEGORY_ABBR'         => $discussion_filters[$topic->FORUM_ID]['abbr'],
        'CATEGORY_LINK'         => append_sid("{$phpbb_root_path}viewforum.$phpEx", 'f=' . $topic->PARENT_ID),
        'AUTHOR_ID'             => $topic->TOPIC_AUTHOR_ID,
        'LAST_POST_TIME'        => $topic->LAST_POST_TIME_RELATIVE,
        'LAST_POSTER'           => $topic->LAST_POST_AUTHOR_FULL,
        'LAST_POSTER_LINK'      => append_sid("{$phpbb_root_path}memberlist.$phpEx", 'mode=viewprofile&u=' . $topic->LAST_POST_AUTHOR_ID . '&style=' . $user->theme['style_id']),
        'LAST_POSTER_AVATAR'    => $topic->LAST_POST_AUTHOR_AVATAR,
        'PAGINATION'            => $topic->PAGINATION,
        'REPLIES'               => number_format($topic->TOPIC_REPLIES),
        'VIEWS'                 => number_format($topic->TOPIC_VIEWS)
	));
}
 
/*
* ==============================================================================
* Assign community portal template vars.
* ==============================================================================
* 
* Assign gathered information to the template for display.
*/
$template->assign_vars(array(
	'TOTAL_POSTS'	=> number_format($total_posts),
	'TOTAL_TOPICS'	=> number_format($total_topics),
	'TOTAL_USERS'	=> number_format($total_users),
	'NEWEST_USER'	=> get_username_string('full', $config['newest_user_id'], $config['newest_username'], $config['newest_user_colour']),
	'BIRTHDAY_LIST'	=> $birthday_list,
    
	'S_LOGIN_ACTION'			=> append_sid("{$phpbb_root_path}ucp.$phpEx", 'mode=login'),
	'S_DISPLAY_BIRTHDAY_LIST'	=> ($config['load_birthdays']) ? true : false,

	'U_MARK_FORUMS'		        => ($user->data['is_registered'] || $config['load_anon_lastread']) ? append_sid("{$phpbb_root_path}index.$phpEx", 'hash=' . generate_link_hash('global') . '&amp;mark=forums') : '',
	'U_MCP'				        => ($auth->acl_get('m_') || $auth->acl_getf_global('m_')) ? append_sid("{$phpbb_root_path}mcp.$phpEx", 'i=main&amp;mode=front', true, $user->session_id) : '')
);

/*
* ==============================================================================
* Page output.
* ==============================================================================
*/

// Output page contents.
page_header($user->lang['INDEX']);

// Assign identifying tempalte var.
$template->assign_vars(array('PAGE_ID' => 'portal'));

$template->set_filenames(array(
	'body' => 'portal_body.html')
);
page_footer();

?>