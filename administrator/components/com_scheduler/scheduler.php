<?php
defined('_JEXEC') or die;

use Joomla\CMS\MVC\Controller\BaseController;

// Access check.
if (!JFactory::getUser()->authorise('core.manage', 'com_scheduler'))
{
	throw new InvalidArgumentException(JText::sprintf('JERROR_ALERTNOAUTHOR'), 404);
}

// Require the helper
JFactory::getLanguage()->load('com_mkv', JPATH_ADMINISTRATOR . "/components/com_mkv", 'ru-RU', true);
require_once JPATH_ADMINISTRATOR . "/components/com_prj/helpers/prj.php";
require_once JPATH_ADMINISTRATOR . "/components/com_mkv/helpers/mkv.php";
require_once JPATH_ADMINISTRATOR . "/components/com_contracts/helpers/contracts.php";
require_once JPATH_COMPONENT_ADMINISTRATOR . '/helpers/scheduler.php';
$db = JFactory::getDbo();
$db->setQuery("set @TRIGGER_CHECKS=true")->execute();


// Execute the task
$controller = BaseController::getInstance('scheduler');
$controller->execute(JFactory::getApplication()->input->get('task'));
$controller->redirect();
