<?php
use Joomla\CMS\MVC\Model\ListModel;

defined('_JEXEC') or die;

class SchedulerModelTasks extends ListModel
{
    public function __construct($config = array())
    {
        if (empty($config['filter_fields'])) {
            $config['filter_fields'] = array(
                's.id',
                's.date_task', 'dat',
                's.status, s.date_task',
                'company',
                'manager',
                'status',
                'search',
            );
        }
        parent::__construct($config);
        $input = JFactory::getApplication()->input;
        $this->export = ($input->getString('format', 'html') === 'html') ? false : true;
        $this->contractID = $config['contractID'] ?? $input->getInt('contractID', null);
        if (!empty($config['contractID'])) $this->export = true;
    }

    protected function _getListQuery()
    {
        $query = $this->_db->getQuery(true);

        /* Сортировка */
        $orderCol = $this->state->get('list.ordering');
        $orderDirn = $this->state->get('list.direction');
        if ($orderCol == 's.status, s.date_task') {
            if ($orderDirn == 'ASC') $orderCol = 's.status asc, s.date_task';
            if ($orderDirn == 'desc') {
                $orderCol = 's.status desc, s.date_task';
            }
        }

        //Ограничение длины списка
        $limit = (!$this->export) ? $this->getState('list.limit') : 0;

        $query
            ->select("s.*")
            ->select("c.companyID")
            ->select("e.title as company")
            ->select("u.name as manager")
            ->from("#__mkv_scheduler s")
            ->leftJoin("#__mkv_contracts c on c.id = s.contractID")
            ->leftJoin("#__mkv_companies e on e.id = c.companyID")
            ->leftJoin("#__users u on u.id = s.managerID");

        $project = PrjHelper::getActiveProject();
        if (is_numeric($project)) {
            $query->where("c.projectID = {$this->_db->q($project)}");
        }

        $search = (!$this->export) ? $this->getState('filter.search') : JFactory::getApplication()->input->getString('search', '');
        if ($this->contractID === null) {
            if (!empty($search)) {
                if (stripos($search, 'id:') !== false) { //Поиск по ID
                    $id = explode(':', $search);
                    $id = $id[1];
                    if (is_numeric($id)) {
                        $query->where("s.id = {$this->_db->q($id)}");
                    }
                } else {
                    $text = $this->_db->q("%{$search}%");
                    $query->where("(e.title like {$text})");
                }
            }
            $manager = $this->getState('filter.manager');
            if (is_numeric($manager)) {
                $query->where("s.managerID = {$this->_db->q($manager)}");
            }
            $status = $this->getState('filter.status');
            if (is_numeric($status)) {
                $query->where("s.status = {$this->_db->q($status)}");
            }
            $dat = $this->getState('filter.dat');
            if (!empty($dat)) {
                $dat = JDate::getInstance($dat)->format("Y-m-d");
                if ($dat != '0000-00-00') {
                    $query->where("s.date_task = {$this->_db->q($dat)}");
                }
            }
        }
        else {
            $query->where("s.contractID = {$this->_db->q($this->contractID)}");
            $limit = 0;
        }
        if (!SchedulerHelper::canDo('core.edit.all')) {
            $userID = JFactory::getUser()->id;
            $query->where("s.managerID = {$this->_db->q($userID)}");
        }

        $query->order($this->_db->escape($orderCol . ' ' . $orderDirn));
        $this->setState('list.limit', $limit);

        return $query;
    }

    public function getItems()
    {
        $items = parent::getItems();
        $result = ['items' => [-2 => [], 1 => [], 2 => [], 3 => []]];
        $return = PrjHelper::getReturnUrl();
        foreach ($items as $item) {
            $arr = [];
            $arr['id'] = $item->id;
            $color = MkvHelper::getTaskColor($item->status);
            $arr['company'] = $item->company;
            $arr['date_create'] = JDate::getInstance($item->date_create)->format("d.m.Y");
            $arr['date_task'] = JDate::getInstance($item->date_task)->format("d.m.Y");
            $arr['date_close'] = (!empty($item->date_close)) ? JDate::getInstance($item->date_task)->format("d.m.Y") : '';
            $arr['manager'] = MkvHelper::getLastAndFirstNames($item->manager);
            $arr['status'] = "<span style='color: {$color}'>" . JText::sprintf("COM_MKV_TASK_STATUS_{$item->status}") . "</span>";
            $arr['task'] = $item->task;
            $arr['result'] = $item->result;
            $url = JRoute::_("index.php?option={$this->option}&amp;view=tasks&amp;contractID={$item->contractID}");
            $arr['tasks_link'] = JHtml::link($url, $item->task);
            if ($this->contractID > 0) $arr['tasks_link'] = $item->task;
            $url = JRoute::_("index.php?option=com_companies&amp;task=company.edit&amp;id={$item->companyID}&amp;return={$return}");
            $arr['company_link'] = JHtml::link($url, $item->company);
            if (($item->managerID == JFactory::getUser()->id && SchedulerHelper::canDo('core.edit')) || SchedulerHelper::canDo('core.all')) {
                $url = JRoute::_("index.php?option={$this->option}&amp;task=task.edit&amp;id={$item->id}&amp;return={$return}");
                $arr['edit_link'] = JHtml::link($url, JText::sprintf('COM_MKV_HEAD_OPEN'));
            }
            $result['items'][$item->status][] = $arr;
        }
        krsort($result['items'][3]);
        return $result;
    }

    public function getTitle(): string
    {
        if ($this->contractID > 0) {
            $contract = $this->getContract($this->contractID);
            return JText::sprintf('COM_SCHEDULER_TITLE_TASKS_BY_CONTRACT', $contract->company, $contract->project);
        }
        else {
            return JText::sprintf('COM_SCHEDULER_TITLE_TASKS');
        }
    }

    public function getContractID()
    {
        return $this->contractID;
    }

    private function getContract(int $contractID)
    {
        JModelLegacy::addIncludePath(JPATH_ADMINISTRATOR . "/components/com_contracts/models");
        JTable::addIncludePath(JPATH_ADMINISTRATOR . "/components/com_contracts/tables");
        $model = JModelLegacy::getInstance('Contract', 'ContractsModel');
        return $model->getItem($contractID);
    }

    protected function populateState($ordering = 's.status, s.date_task', $direction = 'asc')
    {
        $search = $this->getUserStateFromRequest($this->context . '.filter.search', 'filter_search');
        $this->setState('filter.search', $search);
        $manager = $this->getUserStateFromRequest($this->context . '.filter.manager', 'filter_manager');
        $this->setState('filter.manager', $manager);
        $status = $this->getUserStateFromRequest($this->context . '.filter.status', 'filter_status');
        $this->setState('filter.status', $status);
        $dat = $this->getUserStateFromRequest($this->context . '.filter.dat', 'filter_dat');
        $this->setState('filter.dat', $dat);
        parent::populateState($ordering, $direction);
        PrjHelper::check_refresh();
    }

    protected function getStoreId($id = '')
    {
        $id .= ':' . $this->getState('filter.search');
        $id .= ':' . $this->getState('filter.manager');
        $id .= ':' . $this->getState('filter.status');
        $id .= ':' . $this->getState('filter.dat');
        return parent::getStoreId($id);
    }

    private $export, $contractID;
}
