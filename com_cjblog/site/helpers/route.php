<?php
/**
 * @package     corejoomla.administrator
 * @subpackage  com_cjblog
 *
 * @copyright   Copyright (C) 2009 - 2016 corejoomla.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die();

abstract class CjBlogHelperRoute
{

	protected static $lookup = array();

	protected static $lang_lookup = array();

	public static function getProfileRoute($handle = 0, $language = 0)
	{
// 		$handle = JFilterOutput::stringURLUnicodeSlug($handle);
		$needles = array('profile' => array(0));
		$link = 'index.php?option=com_cjblog&view=profile&id='.$handle;

		if ($language && $language != "*" && JLanguageMultilang::isEnabled())
		{
			self::buildLanguageLookup();
			
			if (isset(self::$lang_lookup[$language]))
			{
				$link .= '&lang=' . self::$lang_lookup[$language];
				$needles['language'] = $language;
			}
		}
		
		if ($item = self::_findItem($needles))
		{
			$link .= '&Itemid=' . $item;
		}
		
		return $link;
	}

	public static function getCategoryRoute ($catid = 'root', $language = 0)
	{
		$category = null;
		$catids = array();
		
		if ($catid instanceof JCategoryNode)
		{
			$id = $catid->id;
			$category = $catid;
		}
		else
		{
			$id = (int) $catid;
			
			if($id > 0)
			{
				$category = JCategories::getInstance('Content')->get($catid);
			}
		}
		
		$needles = array();
		if ($id < 1)
		{
			$link = 'index.php?option=com_cjblog';
			$catids = array(0);
		}
		else
		{
			$link = 'index.php?option=com_cjblog&view=category&id=' . $id;
			if ($category instanceof JCategoryNode)
			{
				$catids = array_reverse($category->getPath());
			}
		}
		
		$needles['category'] = $catids;
		if ($language && $language != "*" && JLanguageMultilang::isEnabled())
		{
			self::buildLanguageLookup();
			
			if (isset(self::$lang_lookup[$language]))
			{
				$link .= '&lang=' . self::$lang_lookup[$language];
				$needles['language'] = $language;
			}
		}
		
		if ($item = self::_findItem($needles))
		{
			$link .= '&Itemid=' . $item;
		}
		
		return $link;
	}

	public static function getFormRoute ($id = 0, $catid = 0)
	{
		// Create the link
		if ($id)
		{
			$link = 'index.php?option=com_cjblog&task=article.edit&t_id=' . $id;
		}
		else
		{
			$link = 'index.php?option=com_cjblog&task=article.add';
		}
		
		if($catid)
		{
			$link = $link . '&catid='.$catid;
		}
		
		return $link;
	}
	
	public static function getCategoriesRoute ($id = 0, $language = 0)
	{
		$needles = array('categories' => array($id));
		$link = 'index.php?option=com_cjblog&view=categories';
	
		if ($language && $language != "*" && JLanguageMultilang::isEnabled())
		{
			self::buildLanguageLookup();
	
			if (isset(self::$lang_lookup[$language]))
			{
				$link .= '&lang=' . self::$lang_lookup[$language];
				$needles['language'] = $language;
			}
		}
	
		if ($item = self::_findItem($needles))
		{
			$link .= '&Itemid=' . $item;
		}
	
		return $link;
	}
	
	public static function getArticlesRoute ($id = null, $language = 0)
	{
		$needles = array('articles' => array(0));
		$link = 'index.php?option=com_cjblog&view=articles';
	
		if ($language && $language != "*" && JLanguageMultilang::isEnabled())
		{
			self::buildLanguageLookup();
	
			if (isset(self::$lang_lookup[$language]))
			{
				$link .= '&lang=' . self::$lang_lookup[$language];
				$needles['language'] = $language;
			}
		}
	
		if ($item = self::_findItem($needles))
		{
			$link .= '&Itemid=' . $item;
		}
		
		return $link;
	}
	
	public static function getApprovalRoute ($id = null, $status = 1, $key)
	{
		$needles = array('articles' => array(0));
		$link = 'index.php?option=com_cjblog';
		
		if($status)
		{
			$link .= '&task=article.approve&key='.$key;
		}
		else 
		{
			$link .= '&task=article.disapprove&key='.$key;
		}
	
		if ($language && $language != "*" && JLanguageMultilang::isEnabled())
		{
			self::buildLanguageLookup();
	
			if (isset(self::$lang_lookup[$language]))
			{
				$link .= '&lang=' . self::$lang_lookup[$language];
				$needles['language'] = $language;
			}
		}
	
		if ($item = self::_findItem($needles))
		{
			$link .= '&Itemid=' . $item;
		}
	
		return $link;
	}

	public static function getLeaderBoardRoute ($id = null, $language = 0)
	{
		$needles = array('leaderboard' => array(0));
		$link = 'index.php?option=com_cjblog&view=leaderboard';
	
		if ($language && $language != "*" && JLanguageMultilang::isEnabled())
		{
			self::buildLanguageLookup();
	
			if (isset(self::$lang_lookup[$language]))
			{
				$link .= '&lang=' . self::$lang_lookup[$language];
				$needles['language'] = $language;
			}
		}
	
		if ($item = self::_findItem($needles))
		{
			$link .= '&Itemid=' . $item;
		}
	
		return $link;
	}

	public static function getSearchRoute ($id = null, $language = 0)
	{
		$needles = array('search' => array(0));
		$link = 'index.php?option=com_cjblog&view=search';
	
		if ($language && $language != "*" && JLanguageMultilang::isEnabled())
		{
			self::buildLanguageLookup();
	
			if (isset(self::$lang_lookup[$language]))
			{
				$link .= '&lang=' . self::$lang_lookup[$language];
				$needles['language'] = $language;
			}
		}
	
		if ($item = self::_findItem($needles))
		{
			$link .= '&Itemid=' . $item;
		}

		return $link;
	}
	
	public static function getUsersRoute ($id = null, $language = 0)
	{
		$needles = array('users' => array(0));
		$link = 'index.php?option=com_cjblog&view=users';
	
		if ($language && $language != "*" && JLanguageMultilang::isEnabled())
		{
			self::buildLanguageLookup();
	
			if (isset(self::$lang_lookup[$language]))
			{
				$link .= '&lang=' . self::$lang_lookup[$language];
				$needles['language'] = $language;
			}
		}
	
		if ($item = self::_findItem($needles))
		{
			$link .= '&Itemid=' . $item;
		}
	
		return $link;
	}
	
	protected static function buildLanguageLookup ()
	{
		if (count(self::$lang_lookup) == 0)
		{
			$db = JFactory::getDbo();
			$query = $db->getQuery(true)
				->select('a.sef AS sef')
				->select('a.lang_code AS lang_code')
				->from('#__languages AS a');
			
			$db->setQuery($query);
			$langs = $db->loadObjectList();
			
			foreach ($langs as $lang)
			{
				self::$lang_lookup[$lang->lang_code] = $lang->sef;
			}
		}
	}

	protected static function _findItem ($needles = null)
	{
		$app      = JFactory::getApplication();
		$menus    = $app->getMenu('site');
		$language = isset($needles['language']) ? $needles['language'] : '*';

		// Prepare the reverse lookup array.
		if (!isset(self::$lookup[$language]))
		{
			self::$lookup[$language] = array();
			$component  = JComponentHelper::getComponent('com_cjblog');
			
			$attributes = array('component_id');
			$values = array($component->id);

			if ($language != '*')
			{
				$attributes[] = 'language';
				$values[]     = array($needles['language'], '*');
			}

			$items = $menus->getItems($attributes, $values);

			foreach ($items as $item)
			{
				if (isset($item->query) && isset($item->query['view']))
				{
					$view = $item->query['view'];

					if (!isset(self::$lookup[$language][$view]))
					{
						self::$lookup[$language][$view] = array();
					}

					if (isset($item->query['id']))
					{
						/**
						 * Here it will become a bit tricky
						 * language != * can override existing entries
						 * language == * cannot override existing entries
						 */
						if (!isset(self::$lookup[$language][$view][$item->query['id']]) || $item->language != '*')
						{
							self::$lookup[$language][$view][$item->query['id']] = $item->id;
						}
					}
					elseif (in_array($view, array('profile', 'articles', 'categories', 'leaderboard', 'users', 'search')))
					{
						if (!isset(self::$lookup[$language][$view][0]) || $item->language != '*')
						{
							self::$lookup[$language][$view][0] = $item->id;
						}
					}
				}
			}
		}
		
		if ($needles)
		{
			foreach ($needles as $view => $ids)
			{
				if (isset(self::$lookup[$language][$view]))
				{
					foreach ($ids as $id)
					{
						if (isset(self::$lookup[$language][$view][(int) $id]))
						{
							return self::$lookup[$language][$view][(int) $id];
						}
					}
				}
			}
		}

		// Check if the active menuitem matches the requested language
		$active = $menus->getActive();

		if ($active && $active->component == 'com_cjblog' && ($language == '*' || in_array($active->language, array('*', $language)) || !JLanguageMultilang::isEnabled()))
		{
			return $active->id;
		}

		// If not found, return language specific home link
		$default = $menus->getDefault($language);

		return !empty($default->id) ? $default->id : null;
	}
}
