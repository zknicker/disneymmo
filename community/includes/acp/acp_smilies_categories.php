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
* @package acp
*/
class acp_smilies_categories
{
	var $u_action;
	var $new_config;

	function main($id, $mode)
	{
		global $db, $user, $auth, $template, $cache, $table_prefix;
		global $config, $phpbb_admin_path, $phpbb_root_path, $phpEx;

		$error = $notify = array();
		$submit = (isset($_POST['submit'])) ? true : false;
		$action = request_var('action', '');
		$cat_id = request_var('c', 0);

		include($phpbb_root_path . 'includes/mods/smilies_categories.' . $phpEx);

		$user->add_lang('mods/smilies_categories');
		$this->tpl_name = 'acp_smilies_categories';
		$this->page_title = 'ACP_SMILIES_CATEGORIES';
		add_form_key('acp_smilies_categories');
		if ($submit && !check_form_key('acp_smilies_categories'))
		{
			trigger_error($user->lang['FORM_INVALID'] . adm_back_link($this->u_action), E_USER_WARNING);
		}

		switch ($action)
		{
			case 'add' :
				if ($submit)
				{
					$data = array(
						'cat_name'		=> request_var('cat_name', '', true),
						'cat_icon'		=> request_var('cat_icon', ''),
					);

					$db->sql_query('INSERT INTO ' . $table_prefix . 'smilies_categories ' . $db->sql_build_array('INSERT', $data));

					$cache->destroy('_smilies_cats');

					trigger_error($user->lang['CAT_ADDED'] . adm_back_link($this->u_action));
				}
				else
				{
					$template->assign_vars(array(
						'CAT_ADD'		=> true,
						'U_ACTION'			=> $this->u_action . '&amp;action=add&amp;c=' . $cat_id,

						'CAT_NAME'		=> request_var('cat_name', '', true),
						'CAT_ICON'		=> request_var('cat_icon', ''),
					));
				}
			break;

			case 'edit' :
				if (!$cat_id)
				{
					trigger_error('NO_CAT');
				}

				if ($submit)
				{
					$data = array(
						'cat_name'		=> request_var('cat_name', '', true),
						'cat_icon'		=> request_var('cat_icon', ''),
					);

					$db->sql_query('UPDATE ' . $table_prefix . 'smilies_categories SET ' . $db->sql_build_array('UPDATE', $data) . ' WHERE cat_id = ' . $cat_id);

					$cache->destroy('_smilies_cats');

					trigger_error($user->lang['CAT_UPDATED'] . adm_back_link($this->u_action));
				}
				else
				{
					$result = $db->sql_query('SELECT * FROM ' . $table_prefix . 'smilies_categories WHERE cat_id = ' . $cat_id);
					$row = $db->sql_fetchrow($result);
					$db->sql_freeresult($result);
					$template->assign_vars(array(
						'CAT_EDIT'			=> true,
						'U_ACTION'			=> $this->u_action . '&amp;action=edit&amp;c=' . $cat_id,

						'CAT_ID'			=> $row['cat_id'],
						'CAT_NAME'			=> $row['cat_name'],
						'CAT_ICON'			=> $row['cat_icon'],
					));
				}
			break;

			case 'delete' :
				if (!$cat_id)
				{
					trigger_error('NO_CAT');
				}

				if (confirm_box(true))
				{
					$db->sql_query('DELETE FROM ' . $table_prefix . 'smilies_categories WHERE cat_id = ' . $cat_id);

					$cache->destroy('_smilies_cats');

					trigger_error($user->lang['CAT_DELETED'] . adm_back_link($this->u_action));
				}
				else
				{
					confirm_box(false, 'DELETE_CAT');
				}
				redirect($this->u_action);
			break;

			default :
				$result = $db->sql_query('SELECT * FROM ' . $table_prefix . 'smilies_categories ORDER BY cat_name ASC');
				while ($row = $db->sql_fetchrow($result))
				{
					$template->assign_block_vars('cats', array(
						'CAT_ID'			=> $row['cat_id'],
						'CAT_NAME'			=> $row['cat_name'],
						'CAT_ICON'			=> str_replace('{SMILIES_PATH}', $phpbb_root_path . $config['smilies_path'] . '/', $row['cat_icon']),

						'U_EDIT'			=> $this->u_action . '&amp;action=edit&amp;c=' . $row['cat_id'],
						'U_DELETE'			=> $this->u_action . '&amp;action=delete&amp;c=' . $row['cat_id'],
					));
				}
				$db->sql_freeresult($result);

				$template->assign_vars(array(
					'CAT_LIST'		=> true,
				));
			break;
		}
	}
}

?>