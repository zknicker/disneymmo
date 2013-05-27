<?php
/**
*
* Smilies Categories 
* User language File[Ebglish]
*
* @package language
* @version $Id: smilies_categories.php 10 2010-03-08 08:41:18Z femu $
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
	'CATEGORY'				=> 'Category',
	'CAT_ADDED'				=> 'The category has been added successfully.',
	'CAT_DELETED'			=> 'The category has been deleted successfully.',
	'CAT_DETAILS'			=> 'Category Details',
	'CAT_ICON'				=> 'Category Icon',
	'CAT_ICON_EXPLAIN'		=> 'Type the full URL of the icon you want displayed for this category.  You may use "{SMILIES_PATH}" to specify the url to the smilies path.',
	'CAT_NAME'				=> 'Category Name',
	'CAT_NAME_EXPLAIN'		=> '',
	'CAT_UPDATED'			=> 'The category has been updated successfully.',
	'CREATE_CAT'			=> 'Create Category',

	'DELETE_CAT'			=> 'Delete Category',
	'DELETE_CAT_CONFIRM'	=> 'Are you sure you want to delete this category?',

	'ICON'					=> 'Icon',

	'NO_CATS'				=> 'No Categories',
	
	// UMIL installation
	'SC_INSERT_FIRST_FILL'					=> 'The tables were filled successfully filled with some basic data.',
	'SC_REMOVE_CONFIG_ENTRIES'				=> 'The entries in the config table were removed successfully',
	'SC_SMILIES_CATEGORIES_NAME'			=> 'Smilies Categories',
	'SC_SMILIES_CATEGORIES_NAME_EXPLAIN'	=> 'With this mod you can categorize your smilies into groups.<br />Click on the below actions to perform, what you like to do. Enabling <strong>Display Full Results</strong> is recommended.<br /><br />Have fun!',
	'SC_UPDATE_SUCCESFUL'					=> 'The tables were updated successfully',
));

?>