<?php
/**
 * @package     corejoomla.site
 * @subpackage  com_cjblog
 *
 * @copyright   Copyright (C) 2009 - 2016 corejoomla.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;
JHtml::_('behavior.tabstate');

require_once JPATH_COMPONENT . '/helpers/constants.php';
require_once JPATH_COMPONENT . '/helpers/route.php';
require_once JPATH_COMPONENT . '/helpers/query.php';
require_once JPATH_COMPONENT . '/lib/api.php';
require_once JPATH_COMPONENT . '/helpers/helper.php';
require_once JPATH_ROOT . '/components/com_content/helpers/route.php';

////////////////////////////////////////// CjLib Includes ///////////////////////////////////////////////
require_once JPATH_ROOT.'/components/com_cjlib/framework.php';
require_once JPATH_ROOT.'/components/com_cjlib/framework/api.php';
CJLib::import('corejoomla.framework.core');
////////////////////////////////////////// CjLib Includes ///////////////////////////////////////////////

if(CjBlogSiteHelper::isUserBanned())
{
	echo JText::_('COM_CJBLOG_YOUR_ACCOUNT_IS_BLOCKED');
}
else
{
	$controller = JControllerLegacy::getInstance('CjBlog');
	$controller->execute(JFactory::getApplication()->input->get('task'));
	$controller->redirect();
}