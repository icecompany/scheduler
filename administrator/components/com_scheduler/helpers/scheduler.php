<?php
defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;

class SchedulerHelper
{
    public static function addSubmenu($vName)
    {
        HTMLHelper::_('sidebar.addEntry', JText::sprintf('COM_SCHEDULER_MENU_TASKS'), 'index.php?option=com_scheduler&view=tasks', $vName === 'tasks');
        PrjHelper::addActiveProjectFilter();
    }

    public static function canDo(string $action): bool
    {
        return JFactory::getUser()->authorise($action, 'com_scheduler');
    }

    public static function getConfig(string $param, $default = null)
    {
        $config = JComponentHelper::getParams("com_scheduler");
        return $config->get($param, $default);
    }
}
