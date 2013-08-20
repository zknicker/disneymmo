<?php
/*
* ==============================================================================
* DisneyMMO EOS Dropzone
*
* For use by DisneyMMO.com
* By Zach Knickerbocker
* ==============================================================================
*
* Server side implementation for the Dropzone on profile pages.
* For more information on Dropzone: http://dropzonejs.com
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

if (!empty($_FILES)) {
     
    $tempFile = $_FILES['profile_header']['tmp_name'];
    uploadProfileImage($tempFile, (int) $user_id);
}
?>