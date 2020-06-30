<?php
defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Table\Table;

class SchedulerHelper
{
    public static function addSubmenu($vName)
    {
        HTMLHelper::_('sidebar.addEntry', JText::sprintf('COM_SCHEDULER_MENU_TASKS'), 'index.php?option=com_scheduler&view=tasks', $vName === 'tasks');
        HTMLHelper::_('sidebar.addEntry', JText::sprintf('COM_SCHEDULER_MENU_NOTIFIES'), 'index.php?option=com_scheduler&view=notifies', $vName === 'notifies');
        PrjHelper::addActiveProjectFilter();
    }

    public static function updateTaskManager(int $contractID, int $managerID): void
    {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $query
            ->update("#__mkv_scheduler")
            ->set("managerID = {$managerID}")
            ->where("contractID = {$contractID}");
        $db->setQuery($query)->execute();
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

    public static function sendNotify(array $data, array $push = []): void
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
        if (!empty($push)) {
            $push['url'] = "https://{$_SERVER['HTTP_HOST']}/administrator/index.php?option=com_scheduler&task=notify.edit&id={$db->insertid()}";
            self::sendPush($push);
        }
    }

    public static function sendPush(array $push)
    {
        $type = 'broadcast';
        if (isset($push['uid'])) $type = 'unicast';
        if (isset($push['uids'])) $type = 'multicast';
        $push['type'] = $type;
        curl_setopt_array($ch = curl_init(), array(
            CURLOPT_URL => "https://pushall.ru/api.php",
            CURLOPT_POSTFIELDS => $push,
            CURLOPT_SAFE_UPLOAD => true,
            CURLOPT_RETURNTRANSFER => true
        ));
        curl_exec($ch); //получить данные о рассылке
        curl_close($ch);
    }
}
