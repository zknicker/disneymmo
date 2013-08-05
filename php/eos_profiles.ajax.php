<?php
/*
* ==============================================================================
* DisneyMMO EOS Profiles - AJAX
*
* For use by DisneyMMO.com
* By Zach Knickerbocker
* ==============================================================================
*
* AJAX handling for profiles.
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
include($phpbb_root_path . '../php/eos.config.php');
include($phpbb_root_path . '../php/eos.functions.php');
include($phpbb_root_path . '../php/eos_profiles.php');

// Start session management.
$user->session_begin();
$auth->acl($user->data);
$user->setup();

$user_id = $user->data['user_id'];
$username = $user->data['username'];
$avatar = $user->data['user_avatar'];
$recipient_id = request_var('recipient', 0);
$message = request_var('message', '', true);

// Error checking
if($user_id !== null && $recipient_id !== null && $message !== null) {

	postWallMessage($message, $recipient_id, $user_id);

	$message = censor_text($message);
	$message = bbcode_nl2br($message);

	$return_data = json_encode(array(
	    'sender' => $username,
	    'sender_url' => append_sid("{$phpbb_root_path}memberlist.$phpEx", 'mode=viewprofile&u=' . $user_id),
	    'sender_avatar' => $phpbb_root_path . 'download/file.php?avatar=' . $avatar,
	    'message' => $message,
	    'date' => 'Just now.'
	));

	echo $return_data;

	returnSuccess($return_data);

} else {
	errorOut("User ID or Recipient ID was null.");
}


/*
* ==============================================================================
* Error Out
* ==============================================================================
*/
function errorOut($message) {

    die(json_encode(array('message' => $message)));
}

/*
* ==============================================================================
* Return Success
* ==============================================================================
*/
function returnSuccess($data) {

	echo $return_data;
}