<?php
/*
* ==============================================================================
* DisneyMMO EOS Showcase Editor Functions
*
* For use by DisneyMMO.com
* By Zach Knickerbocker
* ==============================================================================
*
* Functions for the showcase editor.
*/


include('../../php/eos.config.php');

function postShowcaseJSON($params) {
    
    global $eos_config;

    $showcase_json = json_encode($params);
    $showcase_json_file = $eos_config['showcase_json_path'];
    $fh = fopen($showcase_json_file, 'w');
    fwrite($fh, $showcase_json);
    fclose($fh);
    
}

function getShowcaseJSON() {
    
    global $eos_config;

    $showcase_json = file_get_contents($eos_config['showcase_json_path'], NULL);
    return json_decode($showcase_json, TRUE);    
}

function getParamsForSingleItem($slide) {

    global $eos_config;
    $error = "";

    // Gather parameters from form POST.
    $params = array(
        'type'              => $_POST["type_{$slide}"],
        'title'             => $_POST["title_{$slide}"],
        'title_link'        => $_POST["title_link_{$slide}"],
        'origin'            => $_POST["origin_{$slide}"],
        'origin_link'       => $_POST["origin_link_{$slide}"],
        'caption'           => $_POST["caption_{$slide}"],
        'category'          => $_POST["category_{$slide}"],
        'background_url'    => $_POST["background_url_{$slide}"]
    );
            
    // Check for null fields.
    foreach($params as $param) {
        if(!isset($param)) {
            $error = "A field was left unset. Please fill in all fields.";
            goto out;
        }
    }
        
    // Check for invalid URLs.
    if(!isValidURL($params['background_url']) || !isValidURL($params['title_link']) || !isValidURL($params['origin_link'])) {
    
        $error = "An invalid URL was provided.";
        goto out;
    }
    
    // Check to see if the background is a local image. If not, rehost it.
    if(!isLocalURL($params['background_url'])) {
    
        $filename_ext = pathinfo($params['background_url'], PATHINFO_EXTENSION);
        $filename = base_convert(time(), 10, 36) . '_' . $slide . '.' . $filename_ext;
        $dir = $eos_config['server_root'] . '/' . $eos_config['uploaded_slide_bg_path'];
        if(!uploadExternalImage($params['background_url'], $dir, $filename)) {
        
            $error = "Could not upload background image.";
            goto out;
        
        } else {
        
            $params['background_url'] = $eos_config['public_root'] . $eos_config['uploaded_slide_bg_path'] . $filename;
        }
    }
        
    // Encode HTML entities
    $title = encodeText($params['title']);
    $preview = encodeText($params['origin']);
    $caption = encodeText($params['caption']);
   
out:
    return array($error, $params);
}
?>