<!DOCTYPE html>
<?php

// Define PHPBB.
define('IN_PHPBB', true);
$phpbb_root_path = (defined('PHPBB_ROOT_PATH')) ? PHPBB_ROOT_PATH : '../../community/';
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
$changes_written = false;
$error_writing = '';
$is_admin = false;

// If form submitted, write to DB.
if (!empty($_POST)) {

    for($i = 1; $i <= 4; $i++) {

        $topic_id = $_POST["panel-{$i}-topic-id"];
        $title = $_POST["panel-{$i}-title"];
        $preview = $_POST["panel-{$i}-preview"];
        $category = $_POST["panel-{$i}-category"];
        $cover = $_POST["panel-{$i}-cover"];
    
        // No null fields.
        if(!isset($topic_id, $title, $preview, $category, $cover)) {
        
            $error_writing = "Fields were left unset. Please complete all fields.";
            break;
            
        }
        
        // Topic ID must be a number.
        if(!ctype_digit($topic_id)) {
        
            $error_writing = "Topic ID must be a number.";
            break;
        
        }
    
        // The URL for the cover should be a valid URL (it doesn't need to work).
        if(!preg_match('|^http(s)?://[a-z0-9-]+(.[a-z0-9-]+)*(:[0-9]+)?(/.*)?$|i', $cover)) {
        
            $error_writing = "A valid URL for the cover image must be provided.";
            break;
        
        }
        
        // Encode HTML entities in title and preview.
        $title = encodeText($title);
        $preview = encodeText($preview);
    
        $panel_sql = array(
        
            'topic_id'  => $topic_id,
            'title'     => $title,
            'preview'   => $preview,
            'category'  => $category,
            'cover'     => $cover
        );

        $sql = 'UPDATE phpbb_showcase
            SET ' . $db->sql_build_array('UPDATE', $panel_sql) . '
            WHERE slot = ' . $i;
            
        $db->sql_query($sql); // Handles sanitization
                              // If this is changed, SANITIZE.

    }
    
    if(!$error_writing) { $changes_written = true; }
    
}

// Proceed with generating page if administrator is present.
if ($user->data['is_registered'] && $auth->acl_get('a_')) {

    $is_admin = true;

    // Query for showcase information.
    $showcase_query = 

        "SELECT slot, topic_id, title, preview, category, cover
         FROM phpbb_showcase
         ORDER BY slot";

    // Retrieve query result, and store in array.
    $result = $db->sql_query($showcase_query);
    while ($r = $db->sql_fetchrow($result))	{
        
        // Decode HTML entities in title and preview.
        $title = decodeText($r['title']);
        $preview = decodeText($r['preview']);
        
        $panels[] = array(
            'topic_id'      =>	$r['topic_id'],
            'title'         =>	$title,
            'preview'		=>	$preview,
            'category'		=> 	$r['category'],
            'cover'	        => 	$r['cover']
        );

    }
    
}

?>
<html>
<head>
	
	<meta charset="UTF-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<title>DisneyMMO Control Panel: Showcase Editor</title>
	
	<link rel='stylesheet' href='../../css/master.css' type='text/css' />
	<link rel='stylesheet' href='editor.css' type='text/css' />
	
	<script src='../../js/jquery-1.7.1.min.js' type='text/javascript'></script>
	<script src='../../js/jquery-ui.min.js' type='text/javascript'></script>
	<script src='../../js/html5.js' type='text/javascript'></script>
	<script src='editor.js' type='text/javascript'></script>
    
</head>
<body>

	<header>
		<div class="inner-content">
			<div class="logo">
				<span class="sub-text">disneymmo control panel</span>
				<span class="main-text">showcase editor</span>
			</div>
		</div>
	</header>

<?php

if ($is_admin) {

?>
	
	<section id="intro" class="block">
		<div class="inner-content">
			<p>The showcase editor supports the following basic functions. Use these functions to customize the showcase that appears on the homepage of DisneyMMO.com. Please act responsibly, and don't do anything stupid. Otherwise, you will be hunted down.</p>
			<div class="instruction click"><b>click</b> any of the showcase panels to customize its contents. You will first be required to enter a topic ID for the article set to appear in the showcase.  Next, you will receive automatically generated data which you can customimze should the need arise.</div>
			<div class="instruction drag"><b>drag</b> the showcase panels to rearrange them. Alternatively, you can use the shift function, found on either side of the showcase, to rotate the panels. This is useful when the need arises to add a new panel, and you want to automatically shift the remaining panels to the right.</div>
		</div>
	</section>
	
	<section id="controls" class="block group">
	
		<a href="javascript:;" id="save-changes"><span>Save Changes</span></a>
		<a href="javascript: dese.showcase.refresh();" id="preview-changes"><span>Preview Changes</span></a>
        <div id="notification" style="display: none;"><span></span></div>
	
<?php

    if ($changes_written) {

        echo '<script type="text/javascript">dese.events.changesWritten();</script>';

    }

    else if ($error_writing != '') {

        echo '<script type="text/javascript">dese.events.formError("' . $error_writing . '");</script>';

    }

?>
    
	</section>
	
	<section id="showcase-preview" class="block">

        <a href="javascript: dese.events.shiftLeft();" class="shift-button left"></a>
        <a href="javascript: dese.events.shiftRight();" class="shift-button right"></a>
    
		<div id="showcase" class="block">
			<div class="showcase-view">
				<article class="feature ui-widget-content" id="panel-1">
                    <div class="victim-highlight"></div>
					<div class="showcase-padding">
						<h2 class="news-title"></h2>
						<h3 class="game-title"></h3>
						<p class="news-preview"></p>
					</div>
					<div class="overlay"></div>
					<div class="background"><img src=""></div>
				</article>
				<article class="normal ui-widget-content" id="panel-2">
                    <div class="victim-highlight"></div>
					<div class="showcase-padding">
						<h2 class="news-title"></h2>
						<h3 class="game-title"></h3>
						<p class="news-preview"></p>
					</div>
					<div class="overlay"></div>
					<div class="background"><img src=""></div>
				</article>
                <article class="normal ui-widget-content" id="panel-3">
                    <div class="victim-highlight"></div>
					<div class="showcase-padding">
						<h2 class="news-title"></h2>
						<h3 class="game-title"></h3>
						<p class="news-preview"></p>
					</div>
					<div class="overlay"></div>
					<div class="background"><img src=""></div>
				</article>
                <article class="normal ui-widget-content" id="panel-4">
                    <div class="victim-highlight"></div>
					<div class="showcase-padding">
						<h2 class="news-title"></h2>
						<h3 class="game-title"></h3>
						<p class="news-preview"></p>
					</div>
					<div class="overlay"></div>
					<div class="background"><img src=""></div>
				</article>
			</div>	
		</div>
		
	</section>
	
	<section id="editor" class="block">
        
        <form id="showcase-data" name="showcase-data" action="editor.php" method="post">
    
            <div id="editor-1" class="editor group active">
                <div class="editor-aarow"></div>
                <div class="simple-info">
                    <div class="id-block group">
                        <div class="id-input group">
                            <label for="panel-1-topic-id">topic id</label>
                            <input name="panel-1-topic-id" type="text"></input>
                        </div>
                        <a id="panel-1-generate" class="generate-button" href="javascript:;">generate information</a>
                    </div>
                    <div class="title-input group">
                        <label for="panel-1-title">title</label>
                        <input name="panel-1-title" type="text"></input>
                    </div>
                    <div class="category-select group">
                        <label for="panel-1-category">category</label>
                        <select name="panel-1-category">
                            <option value="dmmo">DisneyMMO</option>
                            <option value="cp">Club Penguin</option>
                            <option value="ph">Pixie Hollow</option>
                            <option value="po">Pirates Online</option>
                            <option value="tt">ToonTown</option>
                        </select>
                    </div>
                </div>
                <div class="preview-info">
                    <div class="preview-input group">
                        <label for="panel-1-preview">preview</label>
                        <textarea name="panel-1-preview"></textarea>
                    </div>
                </div>
                <div class="cover-info">
                    <div class="cover-input group">
                        <label for="panel-1-cover">cover image</label>
                        <input name="panel-1-cover"></input>
                    </div>
                </div>
            </div>
            <div id="editor-2" class="editor group">
                <div class="editor-aarow"></div>
                <div class="simple-info">
                    <div class="id-block group">
                        <div class="id-input group">
                            <label for="panel-2-topic-id">topic id</label>
                            <input name="panel-2-topic-id" type="text"></input>
                        </div>
                        <a id="panel-2-generate" class="generate-button" href="javascript:;">generate information</a>
                    </div>
                    <div class="title-input group">
                        <label for="panel-2-title">title</label>
                        <input name="panel-2-title" type="text"></input>
                    </div>
                    <div class="category-select group">
                        <label for="panel-2-category">category</label>
                        <select name="panel-2-category">
                            <option value="dmmo">DisneyMMO</option>
                            <option value="cp">Club Penguin</option>
                            <option value="ph">Pixie Hollow</option>
                            <option value="po">Pirates Online</option>
                            <option value="tt">ToonTown</option>
                        </select>
                    </div>
                </div>
                <div class="preview-info">
                    <div class="preview-input group">
                        <label for="panel-2-preview">preview</label>
                        <textarea name="panel-2-preview"></textarea>
                    </div>
                </div>
                <div class="cover-info">
                    <div class="cover-input group">
                        <label for="panel-2-cover">cover image</label>
                        <input name="panel-2-cover"></input>
                    </div>
                </div>
            </div>
            <div id="editor-3" class="editor group">
                <div class="editor-aarow"></div>
                <div class="simple-info">
                    <div class="id-block group">
                        <div class="id-input group">
                            <label for="panel-3-topic-id">topic id</label>
                            <input name="panel-3-topic-id" type="text"></input>
                        </div>
                        <a id="panel-3-generate" class="generate-button" href="javascript:;">generate information</a>
                    </div>
                    <div class="title-input group">
                        <label for="panel-3-title">title</label>
                        <input name="panel-3-title" type="text"></input>
                    </div>
                    <div class="category-select group">
                        <label for="panel-3-category">category</label>
                        <select name="panel-3-category">
                            <option value="dmmo">DisneyMMO</option>
                            <option value="cp">Club Penguin</option>
                            <option value="ph">Pixie Hollow</option>
                            <option value="po">Pirates Online</option>
                            <option value="tt">ToonTown</option>
                        </select>
                    </div>
                </div>
                <div class="preview-info">
                    <div class="preview-input group">
                        <label for="panel-3-preview">preview</label>
                        <textarea name="panel-3-preview"></textarea>
                    </div>
                </div>
                <div class="cover-info">
                    <div class="cover-input group">
                        <label for="panel-3-cover">cover image</label>
                        <input name="panel-3-cover"></input>
                    </div>
                </div>
            </div>
            <div id="editor-4" class="editor group">
                <div class="editor-aarow"></div>
                <div class="simple-info">
                    <div class="id-block group">
                        <div class="id-input group">
                            <label for="panel-4-topic-id">topic id</label>
                            <input name="panel-4-topic-id" type="text"></input>
                        </div>
                        <a id="panel-4-generate" class="generate-button" href="javascript:;">generate information</a>
                    </div>
                    <div class="title-input group">
                        <label for="panel-4-title">title</label>
                        <input name="panel-4-title" type="text"></input>
                    </div>
                    <div class="category-select group">
                        <label for="panel-4-category">category</label>
                        <select name="panel-4-category">
                            <option value="dmmo">DisneyMMO</option>
                            <option value="cp">Club Penguin</option>
                            <option value="ph">Pixie Hollow</option>
                            <option value="po">Pirates Online</option>
                            <option value="tt">ToonTown</option>
                        </select>
                    </div>
                </div>
                <div class="preview-info">
                    <div class="preview-input group">
                        <label for="panel-4-preview">preview</label>
                        <textarea name="panel-4-preview"></textarea>
                    </div>
                </div>
                <div class="cover-info">
                    <div class="cover-input group">
                        <label for="panel-4-cover">cover image</label>
                        <input name="panel-4-cover"></input>
                    </div>
                </div>
            </div>
	
        </form>
    
	</section>
    
    <script type='text/javascript'>
    
<?php

        $slot = 1;
        foreach($panels as $panel) {
        
            echo 'dese.editor.populate(' . 
                    
                    $slot               . ',' . 
                    $panel['topic_id']  . ',"' . 
                    $panel['title']     . '","' . 
                    $panel['preview']   . '","' . 
                    $panel['category']  . '","' . 
                    $panel['cover']     . '");';
        
            $slot++;
        }

?>

        dese.showcase.refresh();

    </script>
	
<?php

} else {

?>

    <section id="admin-warning" class="block"> <!-- Holy intruder, Batman! -->
		<div class="inner-content">
			<p>You must be logged in, and an administrator, to view this page. Please authenticate properly at DisneyMMO.com.</p>
		</div>
	</section>

<?php

}

?>    
    
	<footer class="theme-dmmo-1">
		<div id="footer-links" class="group">
			<a href="#">Guidelines</a>
			<a href="#">Contact</a>
			<a href="#">Privacy</a>
			<a href="#">Parents</a>
			<a href="#">Help</a>
			<a href="#">About Us</a>
		</div>
		<div id="footer-rights">
			&copy; 2012 DisneyMMO. Not affiliated with The Walt Disney Company.
		</div>
		<div id="footer-logo"></div>
	</footer>
	
</body>
</html>