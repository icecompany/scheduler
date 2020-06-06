<?php
defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Table\Table;

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

    public static function sendNotify(array $data): void
    {
        if (!isset($data['id']) || $data['id'] === null) {
            $data['date_create'] = JFactory::getDate()->toSql();
            $data['status'] = 0;
            $data['id'] = null;
        }
        $db = JFactory::getDbo();
        $columns = $db->qn(array_keys($data));
        $values = implode(', ', $db->q(array_values($data)));
        $query = $db->getQuery(true);
        $query
            ->insert("#__mkv_notifies")
            ->columns($columns)
            ->values($values);
        $db->setQuery($query)->execute();
    }
}
