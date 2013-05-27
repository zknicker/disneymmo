<?php
/** 
*
* @package Smilies Categories
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

function get_cats()
{
	global $cache, $db, $table_prefix;

	$cats = $cache->get('_smilies_cats');
	if ($cats === false)
	{
		$cats = array();
		$result = $db->sql_query('SELECT * FROM ' . $table_prefix . 'smilies_categories');
		while ($row = $db->sql_fetchrow($result))
		{
			$cats[$row['cat_id']] = $row;
		}
		$db->sql_freeresult($result);

		$cache->put('_smilies_cats', $cats);
	}

	return $cats;
}

function get_smilies_in_cats()
{
	global $cache, $db, $table_prefix;

	$in_cats = $cache->get('_smilies_in_cats');
	if ($in_cats === false)
	{
		$in_cats = array();
		$result = $db->sql_query('SELECT * FROM ' . $table_prefix . 'smilies_in_cats');
		while ($row = $db->sql_fetchrow($result))
		{
			if (!isset($in_cats[$row['cat_id']]))
			{
				$in_cats[$row['cat_id']] = array();
			}
			$in_cats[$row['cat_id']][] = $row['smiley_id'];
		}
		$db->sql_freeresult($result);

		$cache->put('_smilies_in_cats', $in_cats);
	}

	return $in_cats;
}

function get_cats_in_smilies()
{
	global $cache, $db, $table_prefix;

	$in_smilies = $cache->get('_cats_in_smilies');
	if ($in_smilies === false)
	{
		$in_smilies = array();
		$result = $db->sql_query('SELECT * FROM ' . $table_prefix . 'smilies_in_cats');
		while ($row = $db->sql_fetchrow($result))
		{
			if (!isset($in_smilies[$row['smiley_id']]))
			{
				$in_smilies[$row['smiley_id']] = array();
			}
			$in_smilies[$row['smiley_id']][] = $row['cat_id'];
		}
		$db->sql_freeresult($result);

		$cache->put('_cats_in_smilies', $in_smilies);
	}

	return $in_smilies;
}

function smilies_categories_select($name, $selected = array())
{
	$cats = get_cats();

	$select = '<select id="' . $name . '" name="' . $name . ((sizeof($cats) > 1) ? '[]" multiple="yes" size="' . ((sizeof($cats) < 4) ? sizeof($cats) + 1 : 5) . '"' : '"') . '>';

	$select .= smilies_categories_options($selected);

	$select .= '</select>';

	return $select;
}

function smilies_categories_options($selected = array())
{
	$cats = get_cats();

	$select = '<option value="-1">--------------</option>';
	foreach ($cats as $cat)
	{
		$select .= '<option value="' . $cat['cat_id'] . '"' . ((in_array($cat['cat_id'], $selected)) ? 'selected="selected"' : '') . '>' . $cat['cat_name'] . '</option>';
	}

	return $select;
}

function update_smiley_categories($smiley_id, $new_cat_ids, $first = true)
{
	global $cache, $db, $table_prefix;

	$smiley_id = (int) $smiley_id;

	if (!$smiley_id)
	{
		return;
	}

	if ($first)
	{
		$sql = 'SELECT smiley_url FROM ' . SMILIES_TABLE . ' WHERE smiley_id = ' . $smiley_id;
		$db->sql_query($sql);
		$smiley_url = $db->sql_fetchfield('smiley_url');
		$db->sql_freeresult();

		$sql = 'SELECT smiley_id FROM ' . SMILIES_TABLE . '
			WHERE smiley_id <> ' . $smiley_id . '
				AND smiley_url = \'' . $smiley_url . '\'';
		$result = $db->sql_query($sql);
		while ($row = $db->sql_fetchrow($result))
		{
			update_smiley_categories($row['smiley_id'], $new_cat_ids, false);
		}
		$db->sql_freeresult($result);
	}

	$db->sql_query('DELETE FROM ' . $table_prefix . 'smilies_in_cats WHERE smiley_id = ' . $smiley_id);

	if (!is_array($new_cat_ids))
	{
		$new_cat_ids = array($new_cat_ids);
	}

	$multi_sql = array();
	foreach ($new_cat_ids as $cat_id)
	{
		$cat_id = (int) $cat_id;

		if ($cat_id > 0)
		{
			$multi_sql[] = array('cat_id' => $cat_id, 'smiley_id' => $smiley_id);
		}
	}

	if (sizeof($multi_sql))
	{
		$db->sql_multi_insert($table_prefix . 'smilies_in_cats', $multi_sql);
	}

	$cache->destroy('_cats_in_smilies');
	$cache->destroy('_smilies_in_cats');
}

function resync_cats()
{
	global $cache, $db, $table_prefix;

	$cats = get_cats();
	foreach ($cats as $cat)
	{
		$result = $db->sql_query('SELECT count(smiley_id) AS cnt FROM ' . $table_prefix . 'smilies_in_cats WHERE cat_id = ' . $cat['cat_id']);
		$cnt = $db->sql_fetchfield('cnt');
		$db->sql_freeresult($result);

		if ($cnt != $cat['cat_count'])
		{
			$db->sql_query('UPDATE ' . $table_prefix . 'smilies_categories SET cat_count = ' . $cnt . ' WHERE cat_id = ' . $cat['cat_id']);
		}
	}
	$cache->destroy('_smilies_cats');
}
?>