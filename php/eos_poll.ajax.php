<?php
/*
* ==============================================================================
* DisneyMMO EOS Latest Poll AJAX
*
* For use by DisneyMMO.com
* By Zach Knickerbocker
* ==============================================================================
*
* Registers a user's vote to a given poll and returns updated information.
*/

// Define PHPBB.
define('IN_PHPBB', true);
$phpbb_root_path = (defined('PHPBB_ROOT_PATH')) ? PHPBB_ROOT_PATH : '../community/';
$phpEx = substr(strrchr(__FILE__, '.'), 1);

// PHPBB includes.
include($phpbb_root_path . 'common.' . $phpEx);
include($phpbb_root_path . 'includes/functions_display.' . $phpEx);
include($phpbb_root_path . 'includes/bbcode.' . $phpEx);

// DisneyMMO Includes.
include('eos.config.php');

// Start session management.
$user->session_begin();
$auth->acl($user->data);
$user->setup();

// Add language files.
$user->add_lang('viewtopic');

// Is this an update?
$is_update = request_var('update', false);

add_form_key('posting');

/* ==============================================================================
 * FORM SUBMISSION LOGIC
 * ============================================================================== */
if ($is_update)
{
    // Retrieve poll information ------------------------------------------------
    $up_topic_id = request_var('topic_id', 0);
	$up_forum_id = request_var('forum_id', 0);
    
    // Retrieve user's votes ----------------------------------------------------
    $cur_voted_id = explode(',', request_var('cur_voted_id', array('' => 0)));
    $up_vote_id = request_var('vote_id', array('' => 0));

    // Construct query to retrieve the poll being voted on.
	$sql = 'SELECT t.poll_length, t.poll_start, t.poll_vote_change, t.topic_status, f.forum_status, t.poll_max_options
			FROM ' . TOPICS_TABLE . ' t, ' . FORUMS_TABLE . " f
			WHERE t.forum_id = f.forum_id AND t.topic_id = " . (int) $up_topic_id . " AND t.forum_id = " . (int) $up_forum_id;
	
    // Retrieve query result.
    $result = $db->sql_query_limit($sql, 1);
	$data = $db->sql_fetchrow($result);
	$db->sql_freeresult($result);
    
    // Decide whether or not the user can vote on the poll.
	$s_can_up_vote = ($auth->acl_get('f_vote', $up_forum_id) &&
		(($data['poll_length'] != 0 && $data['poll_start'] + $data['poll_length'] > time()) || $data['poll_length'] == 0) &&
		$data['topic_status'] != ITEM_LOCKED &&
		$data['forum_status'] != ITEM_LOCKED &&
		(!sizeof($cur_voted_id) ||
		($auth->acl_get('f_votechg', $up_forum_id) && $data['poll_vote_change']))) ? true : false;
        
    // If the user can vote, orchestrate the vote.
	if($s_can_up_vote) {
    
        // Vote error cases (no votes, or more votes than poll permits, or VOTE_CONVERTED issues).
        if (!sizeof($up_vote_id) || sizeof($up_vote_id) > $data['poll_max_options'] || in_array(VOTE_CONVERTED, $cur_voted_id) || !check_form_key('eos-poll')) {
			$redirect_url = append_sid("{$phpbb_root_path}index.$phpEx");

			meta_refresh(5, $redirect_url);
			if (!sizeof($up_vote_id)) {
            
				$message = 'NO_VOTE_OPTION';
                
			} else if (sizeof($up_vote_id) > $data['poll_max_options']) {
            
				$message = 'TOO_MANY_VOTE_OPTIONS';
			
            } else if (in_array(VOTE_CONVERTED, $cur_voted_id)) {
			
                $message = 'VOTE_CONVERTED';
			
            } else {
            
                $message = 'FORM_INVALID';
			}
            
            echo $user->lang[$message];
            exit();
		}

        // Review vote choices, and update the poll as necessary.
		foreach ($up_vote_id as $option_id) {
        
            // Skip updates if user's vote choice is already recorded.
			if (in_array($option_id, $cur_voted_id)) {
            
				continue;
			}

            // Otherwise, construct query to update the poll with user's vote.
			$sql = 'UPDATE ' . POLL_OPTIONS_TABLE . '
				SET poll_option_total = poll_option_total + 1
				WHERE poll_option_id = ' . (int) $option_id . '
					AND topic_id = ' . (int) $up_topic_id;
			
            // Execute query to update the poll.
            $db->sql_query($sql);

            // For registered users, record additional personal information.
			if ($user->data['is_registered']) {
            
                // Construct insert query.
				$sql_ary = array(
					'topic_id'			=> (int) $up_topic_id,
					'poll_option_id'	=> (int) $option_id,
					'vote_user_id'		=> (int) $user->data['user_id'],
					'vote_user_ip'		=> (string) $user->ip,
				);

				$sql = 'INSERT INTO ' . POLL_VOTES_TABLE . ' ' . $db->sql_build_array('INSERT', $sql_ary);
				
                // Execute query.
                $db->sql_query($sql);
			}
            
		}

        // Review user's previous vote choices, and remove those which are no longer voted for.
		foreach ($cur_voted_id as $option) {
        
			if (!in_array($option, $up_vote_id)) {
            
                // Construct query to remove poll vote.
				$sql = 'UPDATE ' . POLL_OPTIONS_TABLE . '
					SET poll_option_total = poll_option_total - 1
					WHERE poll_option_id = ' . (int) $option . '
						AND topic_id = ' . (int) $up_topic_id;
                        
                // Execute update.
				$db->sql_query($sql);
                
				if ($user->data['is_registered']) {
                
                    // Construct query to delete registered user information from poll for that vote.
					$sql = 'DELETE FROM ' . POLL_VOTES_TABLE . '
						WHERE topic_id = ' . (int) $up_topic_id . '
							AND poll_option_id = ' . (int) $option . '
							AND vote_user_id = ' . (int) $user->data['user_id'];
					
                    // Launch the nuke.
                    $db->sql_query($sql);
				}
			}
		}

        // For guests, record a cookie. Because supporting guests is good.
        // However, the likelihood of DisneyMMO ever supporting this is slim to none.
		if ($user->data['user_id'] == ANONYMOUS && !$user->data['is_bot']) {
        
			$user->set_cookie('poll_' . $up_topic_id, implode(',', $up_vote_id), time() + 31536000);
		}

        // Update last vote time for the topic.
		$sql = 'UPDATE ' . TOPICS_TABLE . '
			SET poll_last_vote = ' . time() . "
			WHERE topic_id = $up_topic_id";
		
        // Execute update.
        $db->sql_query($sql);
	
    }
    
    // Success!
    echo 'success';
    
} else {

    // Unknown issue occurred.
    echo '-1';

}

?>