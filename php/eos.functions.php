<?php
/*
* ==============================================================================
* DisneyMMO EOS Functions
*
* For use by DisneyMMO.com
* By Zach Knickerbocker
* ==============================================================================
*
* Functions for the DisneyMMO EOS homepage and custom environment.
*/

/*
* ==============================================================================
* Construct Showcase Array
* ==============================================================================
* 
* Retrieves the showcase metadata from the special showcase table in the
* phpbb database, and returns its contents in an array.
*/
function constructShowcaseArray() {

    global $eos_config;

    $showcase_json = file_get_contents($eos_config['showcase_json_path'], NULL);
    $showcase_json = json_decode($showcase_json, TRUE);  

    foreach($showcase_json as $slide) {
    
        $panels[] = array(
            'title'             => $slide['title'],
            'title_link'        => $slide['title_link'],
            'origin'            => $slide['origin'],
            'origin_link'       => $slide['origin_link'],
            'caption'           => $slide['caption'],
            'category'          => $slide['category'],
            'background_url'    => $slide['background_url']
        );
    }

    return $panels;
}

/*
* ==============================================================================
* Construct Latest Posts Array
* ==============================================================================
* 
* Retrieves the latest posts and returns them in an array. 'Latest posts'
* refers to those posts with the most recent replies.  'quantity' latest posts
* are returned.
*/
function constructLatestPostsArray($quantity) {

	global $auth, $db;

	// Create an array for the SQL query.
	$latest_posts_sql_array = array(
		'SELECT' => 'p.*, t.*, u.username, u.user_avatar, u.user_avatar_type',

		'FROM' => array(
			POSTS_TABLE => 'p',
		),

		'LEFT_JOIN' => array(
			array(
				'FROM' => array(USERS_TABLE => 'u'),
				'ON' => 'u.user_id = p.poster_id'
			),
			array(
				'FROM' => array(TOPICS_TABLE => 't'),
				'ON' => 'p.topic_id = t.topic_id'
			),
		),

		'WHERE' => $db->sql_in_set('t.forum_id', array_keys($auth->acl_getf('f_read', true))) . '
		AND t.topic_status <> ' . ITEM_MOVED . '
		AND t.topic_approved = 1',

		'ORDER_BY' => 'p.post_id DESC',
	);

	// Build query and query the database.
	$latest_posts = $db->sql_build_query('SELECT', $latest_posts_sql_array);
	$latest_posts_result = $db->sql_query_limit($latest_posts, $quantity);
    $db->sql_freeresult($latest_posts);

	// Compile returned information into an array to return.
	$posts = array();
	while ($r = $db->sql_fetchrow($latest_posts_result)) {
	
		$posts[] = array(
            'forum_id'      => $r['forum_id'],
			'title'         => $r['topic_title'],
			'replies'       => $r['topic_replies'],
			'username'      => $r['username'],
			'avatar'        => get_user_avatar($r['user_avatar'], $r['user_avatar_type'], auto, 34, $r['username']),
            'last_post_id'  => $r['post_id']
		);
	}
	
	return $posts;
}

/*
* ==============================================================================
* Construct Popular Posts Array
* ==============================================================================
* 
* Retrieves the latest posts and returns them in an array. 'Latest posts'
* refers to those posts with the most recent replies.  'quantity' latest posts
* are returned.
*/
function constructPopularPostsArray($quantity) {

	global $auth, $db;

	// Create an array for the SQL query.
	$popular_posts_sql_array = array(
		'SELECT' => 't.*, u.username, u.user_avatar, u.user_avatar_type',

		'FROM' => array(
			TOPICS_TABLE => 't',
		),

		'LEFT_JOIN' => array(
			array(
				'FROM' => array(USERS_TABLE => 'u'),
				'ON' => 'u.user_id = t.topic_last_poster_id'
			),
		),

		'WHERE' => $db->sql_in_set('t.forum_id', array_keys($auth->acl_getf('f_read', true))) . '
		AND t.topic_status <> ' . ITEM_MOVED . '
		AND t.topic_approved = 1',

		'ORDER_BY' => 't.topic_replies DESC',
	);

	// Build query and query the database.
	$popular_posts = $db->sql_build_query('SELECT', $popular_posts_sql_array);
	$popular_posts_result = $db->sql_query_limit($popular_posts, $quantity);
    $db->sql_freeresult($popular_posts);

	// Compile returned information into an array to return.
	$posts = array();
	while ($r = $db->sql_fetchrow($popular_posts_result)) {
	
		$posts[] = array(
            'forum_id'      => $r['forum_id'],
			'title'         => $r['topic_title'],
			'replies'       => $r['topic_replies'],
			'username'      => $r['username'],
			'avatar'        => get_user_avatar($r['user_avatar'], $r['user_avatar_type'], '100%', 34, $r['username']),
            'last_post_id'  => $r['topic_last_post_id']
		);
	}
	
	return $posts;
}

/*
* ==============================================================================
* Construct Articles Array
* ==============================================================================
* 
* Retrieves the articles numbered between 'begin' and 'qty', ordered primarily
* by sticky status, and secondarily by date posted, and returns them and their
* associated information in an array.
*/
function constructArticlesArray($begin, $qty) {
 
	global $db, $user, $eos_config, $phpbb_root_path;
	$posts = array();       /* posts from the DB */
	$articles = array();    /* parsed posts and information to return */
	$bbcode_bitfield = ''; 

	$articles_sql = 

		"SELECT t.forum_id, t.topic_title, t.topic_time, t.topic_replies, t.topic_first_post_id, t.topic_first_poster_name,
                p.topic_id, p.post_id, p.post_text, p.bbcode_bitfield, p.bbcode_uid, a.preview, a.thumbnail
		 FROM " . TOPICS_TABLE . " t INNER JOIN " . POSTS_TABLE . " p LEFT JOIN " . $eos_config['articles_table'] . " a
         ON t.topic_id = a.topic_id
		 WHERE t.topic_id = p.topic_id
			AND p.post_id = t.topic_first_post_id
			AND t.forum_id IN(" . implode(',', $eos_config['articles_source_forums']) . ")
            AND t.topic_id NOT IN(SELECT topic_id FROM " . $eos_config['showcase_table'] . ")
		 ORDER BY t.topic_type DESC, t.topic_time DESC
		 LIMIT " . $begin . ", " . $qty;

	// (1) Retrieve raw query result, and store in array.
	$result = $db->sql_query($articles_sql);
	while ($r = $db->sql_fetchrow($result))	{

		$posts[] = array(
			'topic_id'			=>	$r['topic_id'],
			'topic_time'		=>	$r['topic_time'],
			'topic_title'		=>	$r['topic_title'],
			'topic_author'		=>	$r['topic_first_poster_name'],
			'post_text'			=> 	$r['post_text'],
			'bbcode_uid'		=> 	$r['bbcode_uid'],
			'bbcode_bitfield'	=> 	$r['bbcode_bitfield'],
			'topic_replies' 	=>	$r['topic_replies'],
			'forum_id' 			=> 	$r['forum_id'],
            'preview'           =>  $r['preview'],
            'thumbnail'         =>  $r['thumbnail']
		);

		$bbcode_bitfield = $bbcode_bitfield | base64_decode($r['bbcode_bitfield']);
	}
    $db->sql_freeresult($result);
	
	// Instantiate BBCode object.
	if ($bbcode_bitfield !== '') {
    
	   $bbcode = new bbcode(base64_encode($bbcode_bitfield));
	}

	// (3) Parse retrieved post data for storage as "articles".
    // This step just involves taking care of some edge cases, like
    // no article preview existing, and creating links like that
    // of the "author link".
	foreach($posts as $post) {

        /* Temporary measure to add article entries for homepage posts without them.
        if(!strlen($post['preview']) || !strlen($post['thumbnail'])) {
        
            $temp_preview = getPreviewTextFromPost($post['post_text']);
            createArticleEntry($post['topic_id'], $post['forum_id'], NULL, $temp_preview);
        }*/
        
		$articles[] = array(
			'topic_id'      => $post['topic_id'],
            'forum_id'      => $post['forum_id'],
			'title'         => $post['topic_title'],
			'author'        => $post['topic_author'],
			'author_link'   => 'http://disneymmo.com/community/search.php?author=' . $post['topic_author'],
			'preview'       => decodeText($post['preview']),
            'thumbnail'     => decodeText($post['thumbnail']),
			'time'          => getRelativeTime($post['topic_time']),
			'replies'       => $post['topic_replies'],
			'game'          => getForumAbbreviation($post['forum_id'])
		);
	   
		unset($preview);
	}
	return $articles;
}

/*
* ==============================================================================
* Construct Tweets Array
* ==============================================================================
* 
* Retrieves the latest twitter messages from the tweets json file at
* json/tweets.json.
*/
function constructTweetsArray($quantity) {

    // Retrieve and decode JSON file.
    $tweets_json = file_get_contents('json/tweets.json');
    $tweets_json = json_decode($tweets_json);
    
    // Store relevant information for later sorting.
    $count = 0;
    foreach($tweets_json as $tweet){

        // Retrieve JSON data.
        $status = $tweet->{'status'};
        $status_plain = $tweet->{'status_plain'};
        $username = $tweet->{'username'};
        
        // Find account's name and abbreviation.
        switch($username) {
    
            case 'DisneyMMO':
                $account_name = 'DisneyMMO';
                $account_link = 'http://twitter.com/DisneyMMO';
                $account_abbr = 'dmmo';
                break;
                
            case 'Disney':
                $account_name = 'Disney';
                $account_link = 'http://twitter.com/Disney';
                $account_abbr = 'dis';
                break;
                
            case 'clubpenguin':
                $account_name = 'Club Penguin';
                $account_link = 'http://twitter.com/ClubPenguin';
                $account_abbr = 'cp';
                break;
                
            case 'Pirates_Online':
                $account_name = 'Pirates Online';
                $account_link = 'http://twitter.com/pirates_online';
                $account_abbr = 'po';
                break;
                
            case 'Disney_Toontown':
                $account_name = 'ToonTown';
                $account_link = 'http://twitter.com/Disney_Toontown';
                $account_abbr = 'tt';
                break;
                
        }
        
        $tweets[] = array(
            'status'        => $status,
            'status_plain'  => $status_plain,
            'account_name'  => '@' . $account_name,
            'account_link'  => $account_link,
            'account_abbr'  => $account_abbr
        );
        
        // Only process requested quantity of tweets.
        $count++;
        if($count > $quantity) break;
        
        unset($username, $status, $account_name, $account_abbr);
    }
    
    return $tweets;

}

/*
* ==============================================================================
* Construct Latest Poll Array
* ==============================================================================
* 
* Retrieves the latest poll from the database and returns relevant information
* via an array.
*/
function constructLatestPollArray() {
 
	global $db, $user, $auth, $eos_config;

    // Construct query to get first poll from forums array.
    $sql = 'SELECT t.poll_title, t.poll_start, t.topic_id,  t.topic_first_post_id, t.topic_first_poster_colour, t.topic_first_poster_name, t.topic_poster, t.forum_id, t.poll_length, t.poll_vote_change, t.poll_max_options, t.topic_status, f.forum_status, p.bbcode_bitfield, p.bbcode_uid
            FROM ' . TOPICS_TABLE . ' t, ' . POSTS_TABLE . ' p, ' . FORUMS_TABLE . ' f
            WHERE t.forum_id = f.forum_id AND t.topic_approved = 1 AND t.poll_start > 0
            AND ' . $db->sql_in_set('t.forum_id', $eos_config['poll_source_forums']) . '
            AND t.topic_moved_id = 0
            AND p.post_id = t.topic_first_post_id
            ORDER BY t.poll_start DESC';

    // Get result of query.
    $result = $db->sql_query_limit($sql, 1);

    // If a poll was found:
    if ($result) {

        // Capture data from DB in a variable.
        $data = $db->sql_fetchrow($result);

        // Preliminary setup.
        $poll_has_options = false;              // Does the poll have options? (it better)
        $topic_id = (int) $data['topic_id'];    // Topic ID containing poll.
        $forum_id = (int) $data['forum_id'];    // Forum ID containing poll.
        $cur_voted_id = array();                // Options the user has voted for.
        $s_can_vote = false;                    // Can the user vote?
        
        // Registered users can vote on polls when they've not already voted
        // on them. This is verified by querying the database table which holds
        // records of how users have voted on poll options.
        if ($user->data['is_registered']) {
        
            // Construct query for checking user's voting information.
            $vote_sql = 'SELECT poll_option_id
                FROM ' . POLL_VOTES_TABLE . '
                WHERE topic_id = ' . $topic_id . '
                AND vote_user_id = ' . $user->data['user_id'];
            
            // Retrieve query results.
            $vote_result = $db->sql_query($vote_sql);

            // Get user's votes.
            while ($row = $db->sql_fetchrow($vote_result)) {
            
                $cur_voted_id[] = $row['poll_option_id'];
            }
            
            $db->sql_freeresult($vote_result);
            
        }
        
        // Guests can vote on polls they are not marked (via cookie) as already
        // having voted on. However, this will likely always be disabled, since there
        // is nothing preventing guests from voting multiple times by clearing cookies.
        // While we'll never likely have a use for this logic, it remains in the off
        // chance that we do.
        else {
            
            if (isset($_COOKIE[$config['cookie_name'] . '_poll_' . $topic_id])) {
            
                $cur_voted_id = explode(',', request_var($config['cookie_name'] . '_poll_' . $topic_id, 0, false, true));
                $cur_voted_id = array_map('intval', $cur_voted_id);
            }
        }

        // The user can vote when all of these primary conditions are met:
        //
        //  (1)(a) The user has not voted on this poll AND can vote on polls in this forum.
        //          (OR)
        //     (b) The user can change his votes on polls in this forum AND this poll permits vote changing.
        //      
        //  (2) The poll has not expired, or never expires.
        //  (3) The topic containing the poll is not locked.
        //  (4) The forum containing the poll is not locked.
        $s_can_vote = (((!sizeof($cur_voted_id) && $auth->acl_get('f_vote', $forum_id)) ||
            ($auth->acl_get('f_votechg', $forum_id) && $data['poll_vote_change'])) &&
            (($data['poll_length'] != 0 && $data['poll_start'] + $data['poll_length'] > time()) || $data['poll_length'] == 0) &&
            $data['topic_status'] != ITEM_LOCKED &&
            $data['forum_status'] != ITEM_LOCKED) ? true : false;

        // Construct SQL to retrieve the poll options and associated data for poll.
        $poll_sql = 'SELECT po.poll_option_id, po.poll_option_text, po.poll_option_total
            FROM ' . POLL_OPTIONS_TABLE . " po
            WHERE po.topic_id = {$topic_id}
            ORDER BY po.poll_option_id";

        // Retrieve result of query.
        $poll_result = $db->sql_query($poll_sql);
        $poll_total_votes = 0;
        $poll_data = array();

        // Cycle through poll results to set variables.
        if ($poll_result) {
        
            while($row = $db->sql_fetchrow($poll_result)) {
            
                $poll_has_options = true;
                $poll_data[] = $row;
                $poll_total_votes += $row['poll_option_total'];
            }
        }
        
        $db->sql_freeresult($poll_result);

        // Populate variables for template.
        $poll_url = append_sid("{$phpbb_root_path}viewtopic.$phpEx", "f=$forum_id&t=$topic_id&view=viewpoll#viewpoll");
        $viewtopic_url = append_sid("{$phpbb_root_path}viewtopic.$phpEx", "f=$forum_id&t=$topic_id");
        $poll_end = $data['poll_length'] + $data['poll_start'];

        // Parse the title text.
        if ($data['bbcode_bitfield']) { $poll_bbcode = new bbcode(); }
        else { $poll_bbcode = false; }

        $data['poll_title'] = censor_text($data['poll_title']);

        if ($poll_bbcode !== false) {
        
            $poll_bbcode->bbcode_second_pass($data['poll_title'], $data['bbcode_uid'], $data['bbcode_bitfield']);
        }

        $data['poll_title'] = bbcode_nl2br($data['poll_title']);
        $data['poll_title'] = smiley_text($data['poll_title']);
        unset($poll_bbcode);
        
        // Save data to an array.
        $latest_poll = array('metadata', 'options');
        
        // Returned meta data.
        $latest_poll['metadata'] = array(
            's_poll_has_options'	=> $poll_has_options,
            'poll_question'			=> $data['poll_title'],
            'poll_author'			=> get_username_string('full', $data['topic_poster'], $data['topic_first_poster_name'], $data['topic_first_poster_colour']),
            'poll_time'				=> $user->format_date($data['poll_start']),
            'u_poll_topic'			=> append_sid($phpbb_root_path . 'viewtopic.' . $phpEx . '?t=' . $topic_id . '&f=' . $forum_id),
            'poll_length'			=> $data['poll_length'],
            'topic_id'				=> $topic_id,
            'forum_id'              => $forum_id, /* added for DMMO EOS for poll AJAX update */
            'cur_voted_id'          => implode(',', $cur_voted_id),
            'total_votes'			=> $poll_total_votes,
            'l_max_votes'			=> ($data['poll_max_options'] == 1) ? $user->lang['MAX_OPTION_SELECT'] : sprintf($user->lang['MAX_OPTIONS_SELECT'], $data['poll_max_options']),
            'l_poll_length'			=> ($data['poll_length']) ? sprintf($user->lang[($poll_end > time()) ? 'POLL_RUN_TILL' : 'POLL_ENDED_AT'], $user->format_date($poll_end)) : '',
            'highest_option_pct'    => 0, /* added for DMMO EOS to normalize poll results to 100%, populated later */
            's_can_vote'			=> $s_can_vote,
            's_display_results'		=> true,
            's_is_multi_choice'		=> ($data['poll_max_options'] > 1) ? true : false,
            's_poll_action'			=> 'php/latest.poll.ajax.php?style=4',
            'u_view_results'		=> $poll_url,
            'u_view_topic'			=> $viewtopic_url,
        
        );
        
        // Returned options data.
        foreach($poll_data as $pd) {
        
            // Calculate option's percent of total votes.
            $option_pct = ($poll_total_votes > 0) ? $pd['poll_option_total'] / $poll_total_votes : 0;
            $option_pct_txt = sprintf("%.1d%%", round($option_pct * 100));

            // If option pct is greater than max option pct, update max.
            if($option_pct > $latest_poll['metadata']['highest_option_pct']) {
            
                $latest_poll['metadata']['highest_option_pct'] = $option_pct;
            }
            
            // Parse the option text.
            if ($data['bbcode_bitfield']) { $poll_bbcode = new bbcode(); }
            else { $poll_bbcode = false; }
        
            $pd['poll_option_text'] = censor_text($pd['poll_option_text']);

            if ($poll_bbcode !== false) {
            
                $poll_bbcode->bbcode_second_pass($pd['poll_option_text'], $data['bbcode_uid'], $data['bbcode_bitfield']);
            }

            $pd['poll_option_text'] = bbcode_nl2br($pd['poll_option_text']);
            $pd['poll_option_text'] = smiley_text($pd['poll_option_text']);
            unset($poll_bbcode);

            $latest_poll['options'][] = array(
                'poll_option_id'		=> $pd['poll_option_id'],
                'poll_option_caption'	=> $pd['poll_option_text'],
                'poll_option_result'	=> $pd['poll_option_total'],
                'poll_option_percent'	=> $option_pct_txt,
                'poll_option_pct'		=> round($option_pct * 100),
                'poll_option_img'		=> $user->img('poll_center', $option_pct_txt, round($option_pct * 250)),
                'poll_option_voted'		=> (in_array($pd['poll_option_id'], $cur_voted_id)) ? true : false
            );
            
        }
    }

    $db->sql_freeresult($result);
    
    return $latest_poll;
    
}
    
/*
* ==============================================================================
* Get Preview Text From Post [DEPRECATED - 5/7/13]
* ==============================================================================
* 
* Given the post BBCode, the text for the post's preview as specified in the
* BBCode is returned, providing it exists.
*/
function getPreviewTextFromPost($post) {

    preg_match("/\[preview(.*?)\](.*?)\[\/preview(.*?)\]/ism", $post, $match);
    return $match[2];

}

/*
* ==============================================================================
* Get Cover Image From Post [DEPRECATED - 5/7/13]
* ==============================================================================
* 
* Given the post BBCode, the URL of the cover image is returned. This URL is
* either the one specified by the post, or the default DisneyMMO cover URL.
*/
function getCoverImageFromPost($post) {

    global $eos_config;

    $image_url = "";
    preg_match("/\[cover(.*?)\](.*?)\[\/cover(.*?)\]/ism", $post, $match);
   
    return $match[2];
}

/*
* ==============================================================================
* Get Cover Image From Stock [DEPRECTATED - 5/10/13]
* ==============================================================================
* 
* Retrieve a stock image for a topic given its forum id. If no stock image is
* found, the DisneyMMO default image is returned.
*/
function getCoverImageFromStock($game_abbr) {

    global $eos_config;
    
    // Directory to retrieve random image from.
    $directory = $eos_config['server_root'] . $eos_config['stock_cover_image_path'] . $game_abbr . "/";

    // Using directory, retrieve random image.
    $default_images = glob($directory . "*");
    $result .= $default_images[rand(0, count($default_images) - 1)];
        
    if(empty($result)) {
    
        // Set result to HTTP URL for the DMMO generic image.
        $result = $eos_config['default_cover_image'];
    
    } else {
    
        // Convert random result to HTTP.
        $result = $eos_config['public_root'] . substr($result, strlen($eos_config['server_root']) + 1);
    
    }
    
    return $result;
}

/*
* ==============================================================================
* Get Forum Meta
* ==============================================================================
* 
* Returns forum meta information given the forum's ID.
*/
function getForumMeta($forum_id) {

    global $db, $eos_config;
    
    $sql = 'SELECT * FROM phpbb_discussion_filters WHERE forum_id = ' . (int) $forum_id;
    $result = $db->sql_query($sql);
    $row = $db->sql_fetchrow($result);

    return array(
    
        'abbr'      => $row['abbr'],
        'category'  => $row['category'],
        'icon_path' => $phpbb_root_path . '../' . $eos_config['filter_icon_path'] . $row['icon']
    );
}

/*
* ==============================================================================
* Encode Text (UTF-8)
* ==============================================================================
* 
* Encodes a string of text.
*/
function encodeText($string) {

    return htmlentities($string, ENT_QUOTES, 'UTF-8');
}

/*
* ==============================================================================
* Decode Text (UTF-8)
* ==============================================================================
* 
* Decodes a string of text.
*/
function decodeText($string) {

    return html_entity_decode($string, ENT_QUOTES, 'UTF-8');
}

/*
* ==============================================================================
* Get Shortened Text
* ==============================================================================
* 
* Shortens the text to a length of $length characters. An ellipses is appended
* _afterwards_.
*
* Based off of an implementation by Elliott Brueggeman:
* http://www.ebrueggeman.com/blog/abbreviate-text-without-cutting-words-in-half
*/
function getShortenedText($input, $length) {
  
    // If text is already within length, return it.
    if (strlen($input) <= $length) {
    
        return $input;
    }
  
    // Trim text at position of last space within desired text length.
    $last_space = strrpos(substr($input, 0, $length), ' ');
    $trimmed_text = substr($input, 0, $last_space);
  
    // Add ellipses.
    $trimmed_text .= "&#8230";
  
    return $trimmed_text;
}

/*
* ==============================================================================
* Strip Images
* ==============================================================================
* 
* Strips images from a post.
*/
function stripImages($text) {
  
    global $config;
    
    $pattern = '/<img(.*?)>/s';
    return preg_replace($pattern, '', $text);
    
}

/*
* ==============================================================================
* Get Relative Time
* ==============================================================================
* 
* Returns a relative time string given a time stamp (e.g. 2 hours ago).
*/
function getRelativeTime($ts) {
  
    if(!ctype_digit($ts))
        $ts = strtotime($ts);

    $diff = time() - $ts;
    if($diff == 0)
        return 'now';
        
    elseif($diff > 0) {
    
        $day_diff = floor($diff / 86400);
        if($day_diff == 0)
        {
            if($diff < 60) return 'Seconds ago';
            if($diff < 120) return '1 minute ago';
            if($diff < 3600) return floor($diff / 60) . ' minutes ago';
            if($diff < 7200) return '1 hour ago';
            if($diff < 86400) return floor($diff / 3600) . ' hours ago';
        }
        if($day_diff == 1) return 'Yesterday at ' . date('g:ia', $ts);
        if($day_diff < 7) return $day_diff . ' days ago';
        if($day_diff < 31) return ceil($day_diff / 7) . ' weeks ago';
        if($day_diff < 60) return 'last month';
        return date('F Y', $ts);
        
    } else {
    
        $diff = abs($diff);
        $day_diff = floor($diff / 86400);
        if($day_diff == 0) {
        
            if($diff < 120) return 'in a minute';
            if($diff < 3600) return 'in ' . floor($diff / 60) . ' minutes';
            if($diff < 7200) return 'in an hour';
            if($diff < 86400) return 'in ' . floor($diff / 3600) . ' hours';
        }
        if($day_diff == 1) return 'Tomorrow';
        if($day_diff < 4) return date('l', $ts);
        if($day_diff < 7 + (7 - date('w'))) return 'next week';
        if(ceil($day_diff / 7) < 4) return 'in ' . ceil($day_diff / 7) . ' weeks';
        if(date('n', $ts) == date('n') + 1) return 'next month';
        return date('F Y', $ts);
    }
}

$log_file_path = $eos_config['server_root'] . '/' . $eos_config['articles_thumbnails_dir'] . 'log.txt';
$log_file = null; /* reference to log file for reading/writing */

/*
* ==============================================================================
* Is Forum An Article Poster
* ==============================================================================
* 
* Returns true if the given forum ($forum_id) has its threads posted as
* an article.
*/
function isForumAnArticlePoster($forum_id) {

    global $eos_config, $db;

    $sql = 'SELECT posts_articles FROM ' . $eos_config['filters_table'] . ' WHERE forum_id = ' . $forum_id;
    $result = $db->sql_query($sql);
    $result = $db->sql_fetchrow($result);
    return $result['posts_articles'] == 1 ? true : false;
}

/*
* ==============================================================================
* Get Forum Abbreviation
* ==============================================================================
* 
* Returns the forum abbreviation associated with a forum id, $forum_id. If no
* abbreviation is found, the default forum abbreviation is returned.
*/
function getForumAbbreviation($forum_id) {

    global $eos_config, $db;

    $sql = 'SELECT abbr FROM ' . $eos_config['filters_table'] . ' WHERE forum_id = ' . $forum_id;
    $result = $db->sql_query($sql);
    $result = $db->sql_fetchrow($result);
    return strlen($result['abbr']) > 0 ? $result['abbr'] : $eos_config['default_forum_abbr'];
}

/*
* ==============================================================================
* Create Article Entry
* ==============================================================================
* 
* Creates an article entry for the homepage.  This function assumes that a
* correct topic_id is passed. External thumbnail URLs are rehosted. Valid
* image signatures are enforced, the function will fail when a bad image
* for the thumbnail is provided. You can pass any text in for the preview
* text as it is heavily processed here.
*/
function createArticleEntry($topic_id, $forum_id, $thumbnail_url, $preview_text) {

    global $eos_config, $db;
    $error = "";
    
    // Ensure we have a topic_id.
    if(is_null($topic_id)) {
    
        return false;
    }
    
    // If the provided thumbnail is invalid, get a default one.
    if(is_null($thumbnail_url) || !strlen($thumbnail_url) || !isValidURL($thumbnail_url) || !imageIsAcceptable($thumbnail_url)) {
    
        $thumbnail = getDefaultArticleThumbnail($forum_id);
    }
        
    // Rehost external thumbnail URLs, revert to default thumbnail on failure.
    if(!isLocalURL($thumbnail_url)) {
        
        $filename_ext = pathinfo($thumbnail_url, PATHINFO_EXTENSION);
        $filename = base_convert(time(), 10, 36) . '_' . $topic_id . '.' . $filename_ext;
        $dir = $eos_config['server_root'] . '/' . $eos_config['articles_thumbnails_dir'];
        if(!uploadExternalImage($thumbnail_url, $dir, $filename)) {
        
            $thumbnail = getDefaultArticleThumbnail($forum_id);
        
        } else {
        
            $thumbnail = $eos_config['public_root'] . substr($dir . $filename, strlen($eos_config['server_root']) + 1);
        }
    }

    // Sanitize, etc. the preview text.
    $preview = stripImages($preview_text);
    $preview = smiley_text($preview);
    $preview = str_replace("\n", ' ', $preview);
    $preview = str_replace("\t", ' ', $preview);
    $preview = preg_replace("/[[:blank:]]+/", ' ', $preview);
    $preview = censor_text($preview);
    $preview = stripBBCode($preview);
    $preview = trim($preview);
    $preview = getShortenedText($preview, $eos_config['articles_max_preview_length']);

    // Post to the DB (also determine if it is an update or an insert).
    $sql_arr = array(
        'topic_id'      => $topic_id,
        'preview'       => encodeText($preview),
        'thumbnail'     => encodeText($thumbnail)
    );

    // See whether or not topic_id is in the table already.
    $sql ='SELECT count(topic_id) AS total FROM ' . $eos_config['articles_table'] . ' WHERE topic_id = ' . (int) $topic_id;
    $result = $db->sql_query($sql);
    $count = (int) $db->sql_fetchfield('founder_count');
    $db->sql_freeresult($result);
    
    if($count == 0) {
        $sql = 'INSERT INTO ' . $eos_config['articles_table'] . ' ' . $db->sql_build_array('INSERT', $sql_arr);
    } else {
        $sql = 'UPDATE ' . $eos_config['articles_table'] . ' ' . $db->sql_build_array('UPDATE', $sql_arr) . ' WHERE topic_id = ' . (int) $topic_id;
    }
    
    $db->sql_query($sql);
    
    return true;
}

/*
* ==============================================================================
* Get Default Article Thumbnail
* ==============================================================================
* 
* Given a forum id, $forum_id, a default article thumbnail URL is returned.
* The forum id is required in order to determine was kind of default thumbnail
* to return.
*/
function getDefaultArticleThumbnail($forum_id) {

    global $eos_config;

    // Generate default article thumbnails directory.
    $abbr = getForumAbbreviation($forum_id);
    $directory = $eos_config['server_root'] . "/" . $eos_config['articles_def_thumbnails_dir'] . $abbr . "/";

    // Retrieve relevant default thumbnail image.
    $default_images = glob($directory . "*");
    $result = $default_images[rand(0, count($default_images) - 1)];

    if (empty($result)) {
    
        $result = $eos_config['public_root'] . $eos_config['articles_def_thumbnail'];

    } else {
        
        // Convert random result to HTTP.
        $result = $eos_config['public_root'] . substr($result, strlen($eos_config['server_root']) + 1);
    }
    
    return $result;
}

/*
* ==============================================================================
* Write External Image
* ==============================================================================
* 
* Store an image from $url at a $server_location on the server using $file_name
* for the new image name.
*/
function uploadExternalImage($url, $server_location, $file_name) {

    global $eos_config;

    $ret = false;
    $cwd = getcwd();
        
    // Retrieve the foreign image.
    if(imageIsAcceptable($url)) {
    
        $image = file_get_contents($url, false, NULL, 0,
                $eos_config["upload_max_image_length"]);
    }
    
    if($image && ($image !== false)) {
    
        // Write the image.
        chdir($server_location);
        $fh = fopen($file_name, 'w');
        if(fwrite($fh, $image) !== false) {
            $ret = true;
        }
        fclose($fh);    
        chdir($cwd);
    }
    
    return $ret;
}

/*
 * ==============================================================================
 * Is Valid URL
 * ==============================================================================
 * 
 * Returns true when the passed URL is a correctly formatted URL.
 */
function isValidURL($url) {

    return preg_match('|^http(s)?://[a-z0-9-]+(.[a-z0-9-]+)*(:[0-9]+)?(/.*)?$|i', $url);
}

/*
 * ==============================================================================
 * Is Local URL
 * ==============================================================================
 * 
 * Returns true when the passed URL is a local URL (determined by server
 * settings in the EOS config file).
 */
function isLocalURL($url) {

    global $eos_config;

    $host = parse_url($url, PHP_URL_HOST);
    return (strpos($host, $eos_config['website_name']) !== false);
}

/*
 * ==============================================================================
 * Image Is Acceptable
 * ==============================================================================
 * 
 * Check an image's attributes to ensure that is is safe. This is not
 * necessarily 100% secure, and should be expanded upon in the future.
 *
 * Works from the cwd.
 */
function imageIsAcceptable($image_url) {

    if (!strlen($image_url)) {
    
        return false;
    }

    $allowedFormats = array(
        'image/jpeg',
        'image/jpg',
        'image/png',
        'image/bmp',
        'image/gif'
    );
    
    $imageData = getimagesize($image_url);
    $imageFileType = $imageData['mime'];
    
    if (!in_array($imageFileType, $allowedFormats)) {
    
        return false;
        
    } else {
    
        return true;
    }
}

/*
 * ==============================================================================
 * Strip BBCode
 * ==============================================================================
 * 
 * Strips BBCode from text.
 */
function stripBBCode($text) {

    $pattern = "#\[([^\]]+?)(=[^\]]+?)?\](.*?)\[/\1\]#"; 
    $replace = "$3"; 
    return preg_replace($pattern, $replace, $text); 
}
?>