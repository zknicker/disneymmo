<?php
/*
* ==============================================================================
* DisneyMMO EOS Profiles - AJAX
*
* For use by DisneyMMO.com
* By Zach Knickerbocker
* ==============================================================================
*
* Library functions for PHPBB profiles for DMMO EOS.
*/

if (!isset($phpbb_root_path)) {
	die('This is not a PHPBB environment.');
}

require_once($phpbb_root_path . 'includes/functions_display.' . $phpEx);
require_once($phpbb_root_path . '../php/eos.config.' . $phpEx);
require_once($phpbb_root_path . '../php/eos.functions.' . $phpEx);

/*
* ==============================================================================
* Get Wall Messages starting at $start and ending at $start + $qty.
* ==============================================================================
*/
function getWallMessages($recipient_id, $start, $qty) {

	global $phpbb_root_path, $user, $db, $eos_config;

	// Retrieve messages for recipient.
	$sql_array = array(
	    'SELECT' => 'u.username, u.user_id, u.user_avatar, u.user_avatar_type, u.user_avatar_height, u.user_avatar_width, m.message_text, m.message_time',

	    'FROM' => array(
	        $eos_config['profile_wall_messages_table'] => 'm',
	        $eos_config['profile_wall_messages_track_table'] => 'mt',
	        USERS_TABLE => 'u'
	    ),

	    'WHERE' => 'm.message_id = mt.message_id
	        AND mt.recipient_id = ' . (int) $recipient_id . '
	        AND u.user_id = mt.sender_id'
	);

	$sql = $db->sql_build_query('SELECT', $sql_array) . ' ORDER BY m.message_time DESC LIMIT ' . $start . ', ' . $qty;
	$result = $db->sql_query($sql);

	// Assemble messages return object.
	while($row = $db->sql_fetchrow($result)) {

		$message = $row['message_text'];
		$message = censor_text($message);
		$message = bbcode_nl2br($message);

		$messages[] = array(
			'sender' => get_username_string('full', $row['user_id'], $row['username'], $data['user_colour']),
            'sender_username' => get_username_string('username', $row['user_id'], $row['username'], $data['user_colour']),
            'sender_url' => get_username_string('profile', $row['user_id'], $row['username'], $data['user_colour']),
			'sender_avatar' => get_user_avatar($row['user_avatar'], $row['user_avatar_type'], $row['user_avatar_width'], $row['user_avatar_height']),
			'message' => $message,
			'date' => $row['message_time']
		);
    }
    $db->sql_freeresult($result);

    return $messages;
}

/*
* ==============================================================================
* Get Messages For PHPBB Template
* ==============================================================================
*/
function getMessagesForPHPBBTemplate($messages) {

	global $template, $user;

	foreach ($messages as $message) {

		$template->assign_block_vars('wall_messages', array(
			'SENDER' => $message['sender'],
			'SENDER_AVATAR' => $message['sender_avatar'],
			'MESSAGE' => $message['message'],
			'DATE' => $user->format_date($message['date']),
			'DATE_REL' => getRelativeTime($message['date'])
		));
	}
}

/*
* ==============================================================================
* Gets the initial profile message for PHPBB templates.
* ==============================================================================
*/
function getInitialMessageForPHPBBTemplate() {

	global $template;

	$template->assign_block_vars('wall_messages', array(
        'MESSAGE' => "No messages found! Maybe you can leave the first one? Type a message into the input box and press 'enter'."
    ));
}

/*
* ==============================================================================
* Post Wall Message
* ==============================================================================
*/
function postWallMessage($message, $sender_id, $recipient_id) {

	global $db, $eos_config;

	// Update messages table (note: db sanitization is handled by sql_build_array)
	$sql_arr = array(
	    'message_text'	=> $message,
	    'message_time'	=> time()
	);

	$sql = 'INSERT INTO ' . $eos_config['profile_wall_messages_table'] . ' ' . $db->sql_build_array('INSERT', $sql_arr);
	$db->sql_query($sql);
	$message_id = $db->sql_nextid();

	// Update messages tracking table.
	$sql_arr = array(
	    'sender_id'		=> (int) $sender_id,
	    'recipient_id'	=> (int) $recipient_id,
	    'message_id'	=> (int) $message_id
	);

	$sql = 'INSERT INTO ' . $eos_config['profile_wall_messages_track_table'] . ' ' . $db->sql_build_array('INSERT', $sql_arr);
	$db->sql_query($sql);
}

/*
* ==============================================================================
* Moves a profile image to its permanent storage space.
* ==============================================================================
*/
function uploadProfileImage($tempFile, $user_id) {

	global $eos_config;
    
    $targetPath = $eos_config['profile_banner_dir'];
    $targetFile =  $targetPath . (int) $user_id;

    move_uploaded_file($tempFile, $targetFile);
}