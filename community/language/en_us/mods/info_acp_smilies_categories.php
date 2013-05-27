<?php
/**
*
* Smilies Categories 
* User language File[English]
*
* @package language
* @version $Id: info_acp_smilies_categories.php 10 2010-03-08 08:41:18Z femu $
* @copyright (c) 2009, 2010 ExReaction, wuerzi & femu
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

/**
* @ignore
*/
if (!defined('IN_PHPBB'))
{
	exit;
}

// Create the lang array if it does not already exist
if (empty($lang) || !is_array($lang))
{
	$lang = array();
}

// Merge the following language entries into the lang array
$lang = array_merge($lang, array(
	'ACP_SMILIES_CATEGORIES'	=> 'Smilies Categories',
));

?>