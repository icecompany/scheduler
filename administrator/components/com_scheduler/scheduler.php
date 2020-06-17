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
JFactory::getLanguage()->load('com_companies', JPATH_ADMINISTRATOR . "/components/com_companies", 'ru-RU', true);
require_once JPATH_ADMINISTRATOR . "/components/com_prj/helpers/prj.php";
require_once JPATH_ADMINISTRATOR . "/components/com_mkv/helpers/mkv.php";
require_once JPATH_ADMINISTRATOR . "/components/com_contracts/helpers/contracts.php";
require_once JPATH_ADMINISTRATOR . "/components/com_companies/helpers/companies.php";
require_once JPATH_COMPONENT_ADMINISTRATOR . '/helpers/scheduler.php';
require_once JPATH_ADMINISTRATOR . '/components/com_companies/passwd.php';
$db = JFactory::getDbo();
$passwd = $db->q($credentials->password);
$db->setQuery("SELECT @pass:={$passwd}")->execute();

// Execute the task
$controller = BaseController::getInstance('scheduler');
$controller->execute(JFactory::getApplication()->input->get('task'));
$controller->redirect();
