<?php
define('IN_PHPBB', true);
$phpbb_root_path = (defined('PHPBB_ROOT_PATH')) ? PHPBB_ROOT_PATH : './';
$phpEx = substr(strrchr(__FILE__, '.'), 1);
include($phpbb_root_path . 'common.' . $phpEx);

// Start session management
$user->session_begin();
$auth->acl($user->data);
$user->setup();

if ($user->data['user_id'] == ANONYMOUS)
{
    login_box('', $user->lang['LOGIN']);
} 

page_header('Chat');

// Variables to be sent to template.
$template->assign_vars(array(
    'CHAT_USERNAME'        => $user->data['username']
));

$template->set_filenames(array(
	'body' => 'chat.html',
    'PAGE_ID' => 'chat'
));

print_r($template);

make_jumpbox(append_sid("{$phpbb_root_path}viewforum.$phpEx"));
page_footer();
?>