<?php
/**
 * @package     corejoomla.administrator
 * @subpackage  plg_content_cjblog
 *
 * @copyright   Copyright (C) 2009 - 2016 corejoomla.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

require_once JPATH_ROOT.'/components/com_content/helpers/route.php';
require_once JPATH_ROOT.'/components/com_cjblog/helpers/constants.php';
require_once JPATH_ROOT.'/components/com_cjblog/helpers/route.php';
require_once JPATH_ROOT.'/components/com_cjblog/helpers/helper.php';

class PlgContentCjBlog extends JPlugin
{
	public function __construct(& $subject, $config)
	{
		parent::__construct($subject, $config);
	}
	
	public function onContentPrepare($context, &$article, &$params, $page = 0)
	{
		if ($context != 'com_content.article' || JFactory::getApplication()->isAdmin())
		{
			return true;
		}

		require_once JPATH_ROOT.'/components/com_cjlib/framework.php';
		require_once JPATH_ROOT.'/components/com_cjlib/framework/api.php';
		CJLib::import('corejoomla.framework.core');
		
		$custom_tag			= false;
		CJLib::behavior('bscore', array('customtag'=>$custom_tag));
		CJFunctions::load_jquery(array('libs'=>array('fontawesome'), 'custom_tag'=>$custom_tag));
		
		$this->loadLanguage('com_cjblog', JPATH_ROOT);
		$document = JFactory::getDocument();
		$document->addStyleSheet(CJBLOG_MEDIA_URI.'css/cj.blog.min.css');
		
		// reset article params
		$article->params->set('show_title', 		false);
		$article->params->set('show_author', 		false);
		$article->params->set('show_print_icon', 	false);
		$article->params->set('show_email_icon', 	false);
		$article->params->set('access-edit', 		false);

		$appParams			= JComponentHelper::getParams('com_cjblog');
		$layout				= $appParams->get('ui_layout', 'default');
		
		// initiate blocks
		$toolbarHtml 		= '';
		$titleHtml			= '';
		$authorInfoHtml		= '';
		$socialSharingHtml	= '';
		
		if($appParams->get('show_toolbar'))
		{
			$toolbarHtml 	= CjBlogSiteHelper::renderLayout($layout.'.toolbar', array('params'=>$appParams));
		}
		
		if($appParams->get('show_title'))
		{
			$titleHtml 		= '<div class="page-header no-space-top"><h2 class="no-space-top" itemprop="name">'.CjLibUtils::escape($article->title).'</h2></div>';
		}
		
		//*************************** SOCIAL SHARING *****************************//
		if($params->get('social_sharing', 1) == 1)
		{
			JPluginHelper::importPlugin( 'corejoomla' );
			$dispatcher 	= JEventDispatcher::getInstance();
			$results 		= $dispatcher->trigger( 'onSocialsDisplay', array( 'com_communityanswers.question', $params ) );
								
			if(!empty($results))
			{
				$socialSharingHtml = '<p class="text-muted">'.JText::_('COM_CJBLOG_SOCIAL_SHARING_DESC').'</p>' . implode(' ', $results);
			}
		}
		//************************ END SOCIAL SHARING ***************************// 
		
		//************************* AUTHOR INFO *********************************//
		if($appParams->get('show_author_info'))
		{
			require_once JPATH_ROOT.'/components/com_cjblog/lib/api.php';
			$profileApi 	= CjBlogApi::getProfileApi();
			$profile 		= $profileApi->getUserProfile($article->created_by);
			$aboutTextApp 	= $appParams->get('about_text_app', 'cjblog');
			
			if($aboutTextApp == 'easyprofile')
			{
				$db = JFactory::getDbo();
				$query = $db->getQuery(true)
					->select($db->qn($db->escape($appParams->get('easyprofile_about_field', 'author_info'))).' AS about')
					->from('#__jsn_users')
					->where('id = '. $article->created_by);
				
				$db->setQuery($query);
				$profile['about'] = $db->loadResult();
			}
			
			$authorInfoHtml = CjBlogSiteHelper::renderLayout($layout.'.author_info', array('article'=>$article, 'profile'=>$profile,'params'=>$appParams));
		}
		//*********************** END AUTHOR INFO *********************************//
		
		//************************ ARTICLE IMAGES *********************************//
		$images  	= json_decode($article->images);
		$imagesHtml = '';
		if (isset($images->image_fulltext) && !empty($images->image_fulltext))
		{
			$imgfloat = (empty($images->float_fulltext)) ? $params->get('float_fulltext') : $images->float_fulltext;
			$imagesHtml = $imagesHtml . '<div class="pull-'.htmlspecialchars($imgfloat).' item-image"> <img ';
			if ($images->image_fulltext_caption)
			{
				$imagesHtml = $imagesHtml . 'class="caption"' . ' title="' . htmlspecialchars($images->image_fulltext_caption) . '"';
			}
			
			$imagesHtml = $imagesHtml . 'src="'.htmlspecialchars($images->image_fulltext).'" alt="'.htmlspecialchars($images->image_fulltext_alt).'" itemprop="image"/> </div>';
			$article->images = null;
		}
		//********************** END ARTICLE IMAGES *******************************//
		
		//************************* ARTICLE TAGS **********************************//
		$info = $params->get('info_block_position', 0);
		$tagsHtml = '';
		if ($info == 0 && $params->get('show_tags', 1) && !empty($article->tags->itemTags)) 
		{
			$tagLayout = new JLayoutFile('joomla.content.tags'); 
			$tagsHtml = $tagLayout->render($article->tags->itemTags);
			$article->tags = null;
		}
		//*********************** END ARTICLE TAGS ********************************//
		
		$article->text 		= '<div id="cj-wrapper">' . $toolbarHtml . $titleHtml . $tagsHtml . $imagesHtml . $article->text . $socialSharingHtml . $authorInfoHtml . '</div>';
	}
	
	public function onContentBeforeSave($context, $article, $isNew)
	{
		if ( $context != 'com_content.form' )
		{
			return true;
		}
		
		$user = JFactory::getUser();
		if( ! $user->authorise('core.autoapprove', 'com_cjblog') )
		{
			$article->state = 0;
		}
		
		return true;
	}
	
	public function onContentAfterSave($context, $article, $isNew)
	{
		if ($context != 'com_content.form')
		{
			return true;
		}
	
		$user = JFactory::getUser();
		$db = JFactory::getDbo();
	
		if (!$isNew || $user->authorise('core.autoapprove', 'com_cjblog'))
		{
			return true;
		}
	
		try
		{
			$record 				= new stdClass();
			$record->id 			= $article->id;
			$record->published		= $user->authorise('core.autoapprove', 'com_cjblog') ? $article->state : 3;
			$record->secret_key 	= CjLibUtils::getRandomKey(32);
			
			$db->insertObject('#__cjblog_reviews', $record);
			
			$template = null;
			$tag = JFactory::getLanguage()->getTag();
			
			$query = $db->getQuery(true)
				->select('title, description, language')
				->from('#__cjblog_email_templates')
				->where('email_type = '.$db->q('com_cjblog.approval'))
				->where('language in ('.$db->q($tag).','.$db->q('*').')')
				->where('published = 1');
			
			$db->setQuery($query);
			$templates = $db->loadObjectList('language');
			
			if(isset($templates[$tag]))
			{
				$template = $templates[$tag];
			}
			else if(isset($templates['*']))
			{
				$template = $templates['*'];
			}
				
			if(!empty($template))
			{
				JLoader::import('mail', JPATH_ROOT.'/components/com_cjblog/models');
			
				$user				= JFactory::getUser();
				$config 			= JFactory::getConfig();
				$sitename 			= $config->get('sitename');
				$message 			= new stdClass();
				$mailModel			= JModelLegacy::getInstance( 'mail', 'CjBlogModel' );
				
				$article->slug 		= $article->alias ? ($article->id . ':' . $article->alias) : $article->id;
				$article->catslug 	= !empty($article->category_alias) ? ($article->catid . ':' . $article->category_alias) : $article->catid;
				$approvalUrl		= CjBlogHelperRoute::getApprovalRoute($article->id, true, $key);
				$disapprovalUrl		= CjBlogHelperRoute::getApprovalRoute($article->id, false, $key);
				$articleUrl 		= JRoute::_(ContentHelperRoute::getArticleRoute($article->slug, $article->catslug), false, -1);
			
				$recipients			= array();
				$subject			= str_ireplace('{ARTICLE_TITLE}', 	$article->title, 			$template->title);
				$description 		= str_ireplace('{SITENAME}', 		$sitename, 					$template->description);
				$description 		= str_ireplace('{ARTICLE_TITLE}', 	$article->title, 			$description);
				$description 		= str_ireplace('{ARTICLE_URL}', 	$articleUrl, 				$description);
				$description 		= str_ireplace('{CATEGORY}', 		$article->category_title, 	$description);
				$description		= str_ireplace('{AUTHOR_NAME}', 	$user->$displayName, 		$description);
				$description		= str_ireplace('{APPROVAL_URL}', 	$approvalUrl, 				$description);
				$description		= str_ireplace('{DISAPPROVAL_URL}', $disapprovalUrl, 			$description);
			
				if(!empty($recipients) && !empty($message))
				{
					$message->asset_name 	= $emailType;
					$message->subject 		= $subject;
					$message->description 	= $description;
					
					$mailModel->enqueueMail($message, $recipients, 'none');
				}
			}
		}
		catch(Exception $e)
		{
			return false;
		}
	
		return true;
	}
}