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
$avatar_type = $user->data['user_avatar_type'];
$avatar_width = $user->data['user_avatar_width'];
$avatar_height = $user->data['user_avatar_height'];

$operation = request_var('operation', '', true);

$recipient_id = request_var('recipient', 0);
$message = request_var('message', '', true);

$begin = request_var('begin', 0);
$qty = request_var('qty', 0);

switch ($operation) {

    // Post Message
    case 'postMessage':
        
        if($user_id !== null && $recipient_id !== null && $message !== null) {
            postWallMessage($message, $user_id, $recipient_id);

            $message = censor_text($message);
            $message = bbcode_nl2br($message);

            $return_data = array(
                'sender' => $username,
                'sender_url' => append_sid("{$phpbb_root_path}memberlist.$phpEx", 'mode=viewprofile&u=' . $user_id),
                'sender_avatar' => get_user_avatar($avatar, $avatar_type, $avatar_width, $avatar_height),
                'message' => $message,
                'date' => $user->format_date(time()),
                'date_rel' => getRelativeTime(time())
            );
            
        }else {
            errorOut("Sorry, we made a mistake (ERROR: user ID or recipient ID was null).");
        }
        break;
    
    // Get More Messages
    case 'getMoreMessages':
    
        if($recipient_id !== null) {
            $messages = getWallMessages($recipient_id, $begin, $qty);

            if (count($messages) == 0) {
                errorOut("There are no more messages to retrieve.");
            }
            
            foreach ($messages as $message) {
            
                $return_data[] = array(
                    'sender' => $message['sender_username'],
                    'sender_url' => $message['sender_url'],
                    'sender_avatar' => $message['sender_avatar'],
                    'message' => $message['message'],
                    'date' => $user->format_date($message['date']),
                    'date_rel' => getRelativeTime($message['date'])
                );
            }
            
        }else {
            errorOut("Sorry, we made a mistake (ERROR: No recipient ID specified).");
        }
        break;
        
    // Upload Profile Banner
    case 'uploadProfileBanner':
    
        if (!empty($_FILES)) {        
            $tempFile = $_FILES['profile_header']['tmp_name'];
            uploadProfileImage($tempFile, (int) $user_id);
            $return_data = array('yo' => 'hey');
            
        } else {
            errorOut("Sorry, we made a mistake (ERROR: No file found to upload).");
        }
    
        break;
        
    // Invalid Operation
    default:
    
        errorOut("Sorry, we made a mistake (ERROR: Invalid operation).");
        break;
}

returnSuccess($return_data);


/*
* ==============================================================================
* Error Out
* ==============================================================================
*/
function errorOut($data) {

    header('Content-type: text/html');
    $data_augmented = json_encode(array(
        'error' => true,
        'error_message' => $data
    ));
    
    die($data_augmented);
}

/*
* ==============================================================================
* Return Success
* ==============================================================================
*/
function returnSuccess($data) {

    header('Content-type: text/html');
	$data_augmented = json_encode(array(
        'error' => false,
        'data' => $data
    ));
    
    echo $data_augmented;
}