<?php
/** 
*
* @package Smilies Categories
* @version $Id: acp_smilies_categories.php 10 2010-03-08 08:41:18Z femu $
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

/**
* @package module_install
*/
class acp_smilies_categories_info
{
	function module()
	{
		return array(
			'filename'	=> 'acp_smilies_categories',
			'title'		=> 'ACP_SMILIES_CATEGORIES',
			'version'	=> '1.0.5',
			'modes'		=> array(
				'default'	=> array('title' => 'ACP_SMILIES_CATEGORIES', 'auth' => 'acl_a_icons', 'cat' => array('ACP_MESSAGES')),
			),
		);
	}

	function install()
	{
	}

	function uninstall()
	{
	}
}

?>