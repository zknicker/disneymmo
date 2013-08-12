<!DOCTYPE html>
<?php
/*
* ==============================================================================
* DisneyMMO EOS Showcase Editor
*
* For use by DisneyMMO.com
* By Zach Knickerbocker
* ==============================================================================
*
* Facilitates the editing and management of the DisneyMMO showcase.
* includes: editor.functions.php
*/

// Define PHPBB.
define('IN_PHPBB', true);
$phpbb_root_path = (defined('PHPBB_ROOT_PATH')) ? PHPBB_ROOT_PATH : '../../community/';
$phpEx = substr(strrchr(__FILE__, '.'), 1);

// PHPBB includes.
include($phpbb_root_path . 'common.' . $phpEx);
include($phpbb_root_path . 'includes/functions_display.' . $phpEx);
include($phpbb_root_path . 'includes/bbcode.' . $phpEx);

// DisneyMMO includes.
include('./editor.functions.php');
include('../../php/eos.functions.php');

// Start session management.
$user->session_begin();
$auth->acl($user->data);
$user->setup();

// Variables.
$changes_written = false;
$is_allowed = false;

// When the form is submitted, write changes to the showcase JSON file.
if (!empty($_POST)) {
    $error = "";
    
    for($i = 0; $i <= 4; $i++) {

        if($_POST["type_{$i}"] == 0) {
            list($error, $params[$i]) = getParamsForSingleItem($i);
        }
        else {
            $error = "Invalid slide type was encountered on Slide {$i}!";
        }
        
        if($error) { 
            //break;
        }
    }
    
    $error = NULL;
    if(!$error) {
        
        postShowcaseJSON($params);
    }
}

// Otherwise, retrieve the showcase JSON file to populate the page with.
$slides = getShowcaseJSON();

if ($user->data['is_registered'] && $auth->acl_get('a_')) {
    $is_allowed = true;
}
?>
<html>
<head>
	
	<meta charset="UTF-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<title>DisneyMMO Control Panel: Showcase Editor</title>
	
	<link rel='stylesheet' href='../../css/master_acp.css' type='text/css' />
	<link rel='stylesheet' href='editor.css' type='text/css' />
	
	<script src='../../js/vendor/handlebars.js' type='text/javascript'></script>
    
</head>
<body>

	<header>
		<div class="inside">
			<a href="{DMMO_NAV_HOME}" class="logo">
				<h1 class="logo-screen-reader-text">DisneyMMO</h1>
			</a>
            <div class="scene"></div>
        </div>
	</header>
    <div class="eos-container-whitespace">
    
<?php
if ($is_allowed) {
?>
        <form id="showcase-slides" method="post">
        <section class="generic-content-container">
            <h2 class="title">DisneyMMO Homepage Showcase Editor</h2>
            <p class="body">Welcome to the showcase editor. Configure the showcase presented on DisneyMMO's homepage by modifying the slide parameters below. <b>Please make sure to preview</b> your changes before publishing them to the site.</p>
        </section>
        <section class="generic-content-container">
            <button type="submit" class="button-generic" style="float: left;">Preview Showcase</button><button type="submit" class="button-generic">Publish Changes</button>
        </section>
        <hr></hr>
        <section id="editor-showcase" class="dese-preview-showcase showcase group">
            <ul>
<?php
    for($i = 0; $i <= 4; $i++) {
?>
                <li class="slide">
                    <div class="panel">
                        <div class="slide-meta">
                            <a class="slide-title" href="<?= $slides[$i]['title_link'] ?>"><?= $slides[$i]['title'] ?></a>
                            <a class="slide-origin tag-<?= $slides[$i]['category'] ?>" href="<?= $slides[$i]['origin_link'] ?>"><?= $slides[$i]['origin'] ?></a>
                        </div>
                        <div class="slide-bg"  style="background-image: url(<?= $slides[$i]['background_url'] ?>)"></div>
                    </div>
                    <div class="caption">
                        <?= $slides[$i]['caption'] ?>
                        <div class="progress">
                            <div class="progress-bar">
                        </div>
                    </div>
                </li>
<?php
    }
?>
            </ul>
		</section>
        <hr></hr>
        <div id="dese">
<?php
    for($i = 0; $i <= 4; $i++) {
?>
        <section class="generic-content-container">
            <div id="slide-<?= $i ?>" class="dese-slide-editor" data-slide-no="<?= $i ?>">
                <div class="dese-slide-controls">
                    <h3>Slide <?= $i + 1 ?></h3>
                    <select class="dese-controls-type" name="type_<?= $i ?>" value="<?= $slides[$i]['type'] ?>">
                        <option value="0"<?php if($slides[$i]['type'] == 0) { ?> selected<?php } ?>>Single Item</option>
                    </select>
                    <ul class="dese-controls-bar">
                        <li class="dese-controls-buff" data-icon="&#xe023;" data-tooltip="Buff slide!"></li>
                        <li class="dese-controls-nerf" data-icon="&#xe024;" data-tooltip="Nerf slide!"></li>
                        <li class="dese-controls-clear" data-icon="&#xe025;" data-tooltip="Clear slide's data!"></li>
                        <li class="dese-controls-remove" data-icon="&#xe026;" data-tooltip="Remove slide!"></li>
                    </ul>
                </div>
                <div class="dese-slide-fields">
                    <div class="field-row">
                        <dl class="long">
                            <dt><label for="background_url_<?= $i ?>">Background URL</label></dt>
                            <dd><input type="text" name="background_url_<?= $i ?>" value="<?= $slides[$i]['background_url'] ?>"></dd>
                        </dl>
                    </div>
                    <div class="field-row">
                        <dl>
                            <dt><label for="title_<?= $i ?>">Title</label></dt>
                            <dd><input type="text" name="title_<?= $i ?>" value="<?= $slides[$i]['title'] ?>"></dd>
                        </dl>
                        <dl>
                            <dt><label for="title_link_<?= $i ?>">Title Link</label></dt>
                            <dd><input type="text" name="title_link_<?= $i ?>" value="<?= $slides[$i]['title_link'] ?>"></dd>
                        </dl>
                    </div>
                    <div class="field-row">
                        <dl>
                            <dt><label for="origin_<?= $i ?>">Origin</label></dt>
                            <dd><input type="text" name="origin_<?= $i ?>" value="<?= $slides[$i]['origin'] ?>"></dd>
                        </dl>
                        <dl>
                            <dt><label for="origin_link_<?= $i ?>">Origin Link</label></dt>
                            <dd><input type="text" name="origin_link_<?= $i ?>" value="<?= $slides[$i]['origin_link'] ?>"></dd>
                        </dl>
                    </div>
                    <div class="field-row">
                        <dl>
                            <dt><label for="caption_<?= $i ?>">Caption</label></dt>
                            <dd><input type="text" name="caption_<?= $i ?>" value="<?= $slides[$i]['caption'] ?>"></dd>
                        </dl>
                        <dl>
                            <dt><label for="caption_<?= $i ?>">Category</label></dt>
                            <dd>
                                <select name="category_<?= $i ?>" value="<?= $slides[$i]['category'] ?>" selected="<?= $slides[$i]['category'] ?>">
                                    <option value="dmmo"<?php if($slides[$i]['category'] == 'dmmo') { ?> selected<?php } ?>>DisneyMMO</option>
                                    <option value="cp"<?php if($slides[$i]['category'] == 'cp') { ?> selected<?php } ?>>Club Penguin</option>
                                    <option value="po"<?php if($slides[$i]['category'] == 'po') { ?> selected<?php } ?>>Pirates Online</option>
                                    <option value="ph"<?php if($slides[$i]['category'] == 'ph') { ?> selected<?php } ?>>Pixie Hollow</option>
                                    <option value="tt"<?php if($slides[$i]['category'] == 'tt') { ?> selected<?php } ?>>ToonTown</option>
                                    <option value="dis"<?php if($slides[$i]['category'] == 'dis') { ?> selected<?php } ?>>Disney</option>
                                    <option value="misc"<?php if($slides[$i]['category'] == 'misc') { ?> selected<?php } ?>>Other</option>
                                </select>
                            </dd>
                        </dl>
                    </div>
                </div>
            </div>
        </section>
<?php
    }
?>
        </div>
        </form>
<?php
} else {
?>

        <div class="generic-content-container">
            <h2 class="title">You're not logged in!</h2>
            <p class="body">This page is only available to DisneyMMO staff members. This incident will be reported.</p>
        </div>

<?php
}
?>    
    
    </div>
	<footer class="theme-dmmo-1">
        <section id="footer-extra"></section>
        <section id="footer-content">
            <div id="footer-links" class="group">
                <a href="#">Guidelines</a>
                <a href="#">Contact</a>
                <a href="#">Privacy</a>
                <a href="#">Parents</a>
                <a href="#">Help</a>
                <a href="#">About Us</a>
            </div>
            <div id="footer-rights">
                &copy; 2012 - 2013 DisneyMMO. Not affiliated with The Walt Disney Company.
            </div>
        </section>
	</footer>
    
    <!-- The rest of our JS for optimization awesomeness. -->
    <script src='../../js/vendor/jquery.js' type='text/javascript'></script>
    <script src='../../js/plugins.js' type='text/javascript'></script>
    <script src='../../js/core.js' type='text/javascript'></script>
    <script src='../../js/module/showcase.js' type='text/javascript'></script>
	<script src='editor.js' type='text/javascript'></script>
    
</body>
</html>