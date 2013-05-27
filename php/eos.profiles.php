<?php
/*
* ==============================================================================
* DisneyMMO EOS Profiles
*
* For use by DisneyMMO.com
* By Zach Knickerbocker
* ==============================================================================
*
* Functions for the DisneyMMO profile environment.
*/

include "eos.config.php";

/*
* ==============================================================================
* Main Execution
* ==============================================================================
*/
$can_customize = ($auth->acl_gets('a_user') || $user->data['user_id'] == $user_id ) ? true : false;

$customizations = retrieveProfileCustomizations($user_id);
setProfileCustomizations($customizations);

// Loop through customizations 
foreach($_POST as $key => $value) {

    if(substr($key, 0, 13) == 'customization' && $value != '') {
    
        $type = substr($key, 14);
        uploadProfileCustomization($user_id, $type, $value);
    }
}

$template->assign_vars(array(
    'CAN_CUSTOMIZE'         => $can_customize,
    'SAVE_CUSTOM_ACTION'    => append_sid("{$phpbb_root_path}memberlist.$phpEx", 'mode=viewprofile&u=' . $user_id),
	'POSTS_DAY_NUM'			=> round($posts_per_day, 2),
	'POSTS_PCT_NUM'			=> round($percentage, 2),
));

/*
* ==============================================================================
* Set Profile Customizations
* ==============================================================================
* 
* Modifies the PHPBB Template variable such that the user's customizations
* are present on the profile page.
*/
function setProfileCustomizations($customizations) {

    global $db, $template, $eos_config;
    
    // Get defaults if necessary.
    if(!isset($customizations[0])) {
        $customizations[0] = 'http://stuffpoint.com/bunnies/image/46455-bunnies-cute-and-funnies-rabbit-animal-wallpaper.jpg';
    }
    if(!isset($customizations[1])) {
        $customizations[1] = 'http://stuffpoint.com/bunnies/image/46455-bunnies-cute-and-funnies-rabbit-animal-wallpaper.jpg';
    }
    
    
    $template->assign_vars(array(
        'CUSTOMIZATION_BACKGROUND'     => $customizations[0],
        'CUSTOMIZATION_PROFILE_IMAGE'  => $customizations[1]
    ));
    
}

/*
* ==============================================================================
* Retrieve Profile Customizations
* ==============================================================================
* 
* Retrieves profile customizations from the database.
*/
function retrieveProfileCustomizations($user_id) {

    global $db, $eos_config;
    
    $path = $eos_config['public_root'] . $eos_config['profile_customizations_path'];
    $sql = 'SELECT * FROM ' . $eos_config['profile_customizations_table'] . ' WHERE user_id = ' . (int) $user_id;
    $result = $db->sql_query($sql);
    while($row = $db->sql_fetchrow($result)) {
    
        $customizations[$row['customization_type']] = $path . $row['customization_image'];
    }
    $db->sql_freeresult($result);
    
    return $customizations;
}

/*
* ==============================================================================
* Upload Profile Image
* ==============================================================================
* 
* Upload a profile image binary to a user's profile customizations record.
*/
function uploadProfileCustomization($user_id, $type, $imageName) {

    global $db, $user_id, $eos_config, $can_customize;

    // Validate type.
    $typeIsValid = validateCustomizationType($type);
    
    if($can_customize && $typeIsValid) {
    
        // Move the image to permanent storage.
        $name = $user_id . '-' . $type;
        $success = storeCustomizationImage($imageName, $name);
        if(!$success) { return; }
        
        $sql_ary = array(
            'user_id'               => $user_id,
            'customization_type'    => $type,
            'customization_image'   => $name
        );

        $sql = 'INSERT INTO ' . $eos_config['profile_customizations_table'] . ' (user_id, customization_type, customization_image) ' .
               'VALUES (' . $user_id . ',' . $type . ',"' . $name . '") ' . 
               'ON DUPLICATE KEY UPDATE customization_image = VALUES(customization_image)';
        $db->sql_query($sql);
    }
}

/*
* ==============================================================================
* Validate Customization Type
* ==============================================================================
* 
* Validate a customization type. In the future, move this to a MySQL table.
*/
function validateCustomizationType($type) {

    $isValid = false;
    switch($type) {
    
        case '0':
        case '1':
            $isValid = true;
            break;       
    }
    return $isValid;
}

/*
* ==============================================================================
* Store Customization Image [DEPRECATED - 5/7/13]
* ==============================================================================
* 
* Store the customization image from the previews directory in the permanent
* profile customization images directory.
*/
function storeCustomizationImage($previewName, $newName) {

    global $eos_config;

    $success = false;
    $cwd = getcwd();
    chdir($eos_config['previews_dir']);

    // Write preview image to a new permanent file.
    if(file_exists($previewName) && imageIsValid($previewName)) {
    
        $image = file_get_contents($previewName, 'r');
        chdir($eos_config['profile_customizations_dir']);
        $handle = fopen($newName, 'w');
        fwrite($handle, $image);
        fclose($handle);
        
        $success = true;
    }
    
    chdir($cwd);
    return $success;
}

/*
* ==============================================================================
* Check Image Validity [DEPRECATED - 5/7/13]
* ==============================================================================
* 
* Check the retrieved image for validity (is it safe?)
*
* This is not necessarily 100% secure, and should be expanded upon in
* the future.
*
* Works from the cwd. Make sure this is set appropriately.
*/
function imageIsValid($image) {

    $allowedFormats = array('image/jpeg', 'image/jpg', 'image/png', 'image/bmp');
    $imageData = getimagesize($image);
    $imageFileType = $imageData['mime'];
    
    if (!in_array($imageFileType, $allowedFormats)) {
    
        return false;
        
    } else {
    
        return true;
    }
}