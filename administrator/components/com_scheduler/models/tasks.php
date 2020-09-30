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
                's.date_task',
                's.date_close',
                's.status, s.date_task',
                'company',
                'manager',
                'status',
                'search',
                'date_1',
                'date_2',
            );
        }
        parent::__construct($config);
        $input = JFactory::getApplication()->input;
        $this->export = ($input->getString('format', 'html') === 'html') ? false : true;
        $this->contractID = $config['contractID'] ?? $input->getInt('contractID', null);
        $this->dat = $config['dat'] ?? null;
        if (!empty($config['contractID']) || !empty($config['dat'])) $this->export = true;
        $this->heads = [
            'status_clear' => 'COM_MKV_HEAD_STATUS',
            'date_task' => 'COM_MKV_HEAD_DATE',
            'manager' => 'COM_MKV_HEAD_EXECUTOR',
            'company' => 'COM_MKV_HEAD_COMPANY',
            'task' => 'COM_MKV_HEAD_TASK',
            'result' => 'COM_MKV_HEAD_RESULT',
            'date_close' => 'COM_SCHEDULER_HEAD_TASK_DATE_CLOSE',
        ];
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
        $userID = JFactory::getUser()->id;

        $query
            ->select("s.*")
            ->select("c.companyID, ifnull(c.number_free, c.number) as contract_number, c.status as contract_status_code")
            ->select("cs.title as contract_status")
            ->select("e.title as company")
            ->select("u.name as manager")
            ->from("#__mkv_scheduler s")
            ->leftJoin("#__mkv_contracts c on c.id = s.contractID")
            ->leftJoin("#__mkv_contract_statuses cs on cs.code = c.status")
            ->leftJoin("#__mkv_companies e on e.id = c.companyID")
            ->leftJoin("#__users u on u.id = s.managerID");

        $search = $this->getState('filter.search');
        if ($this->contractID === null && $this->dat === null) {
            $project = PrjHelper::getActiveProject();
            if (is_numeric($project)) {
                $query->where("c.projectID = {$this->_db->q($project)}");
            }
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
            $date_1 = $this->getState('filter.date_1');
            $date_2 = $this->getState('filter.date_2');
            if (!empty($date_1) && empty($date_2)) {
                $dat = JDate::getInstance($date_1)->format("Y-m-d");
                if ($dat != '0000-00-00') {
                    $query->where("s.date_task = {$this->_db->q($dat)}");
                }
            }
            if (!empty($date_1) && !empty($date_2)) {
                $d1 = JDate::getInstance($date_1)->format("Y-m-d");
                $d2 = JDate::getInstance($date_2)->format("Y-m-d");
                if ($d1 != '0000-00-00' && $d2 != '0000-00-00') {
                    $query->where("s.date_task BETWEEN {$this->_db->q($d1)} and {$this->_db->q($d2)}");
                }
            }
        }
        else {
            if ($this->contractID !== null) {
                $query->where("s.contractID = {$this->_db->q($this->contractID)}");
            }
            if ($this->dat !== null) {
                $query
                    ->where("s.date_task = {$this->_db->q($this->dat)}")
                    ->where("s.managerID = {$this->_db->q($userID)}");
            }
            $limit = 0;
        }
        if (!SchedulerHelper::canDo('core.edit.all') && $this->contractID === null) {
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
            $arr['date_close'] = (!empty($item->date_close)) ? JDate::getInstance($item->date_close . "+3 hour")->format("d.m.Y H:i") : '';
            $arr['manager'] = MkvHelper::getLastAndFirstNames($item->manager);
            $arr['status'] = "<span style='color: {$color}'>" . JText::sprintf("COM_MKV_TASK_STATUS_{$item->status}") . "</span>";
            $arr['status_clear'] = JText::sprintf("COM_MKV_TASK_STATUS_{$item->status}");
            $arr['contract_status'] = $item->contract_status ?? JText::sprintf("COM_MKV_STATUS_IN_PROJECT");
            if ($item->contract_status_code == 1 && !empty($item->contract_number)) $arr['contract_status'] = JText::sprintf('COM_MKV_CONTRACT_TITLE_NUMBER', $item->contract_number);
            $url = JRoute::_("index.php?option=com_contracts&amp;task=contract.edit&amp;id={$item->contractID}&amp;return={$return}");
            $arr['contract_link'] = JHtml::link($url, $arr['contract_status']);
            $arr['task'] = $item->task;
            $arr['result'] = $item->result;
            $url = JRoute::_("index.php?option={$this->option}&amp;view=tasks&amp;contractID={$item->contractID}");
            $arr['tasks_link'] = JHtml::link($url, $item->task);
            if ($this->contractID > 0) $arr['tasks_link'] = $item->task;
            $url = JRoute::_("index.php?option=com_companies&amp;task=company.edit&amp;id={$item->companyID}&amp;return={$return}");
            $arr['company_link'] = JHtml::link($url, $item->company);
            if (($item->managerID == JFactory::getUser()->id && SchedulerHelper::canDo('core.edit')) || SchedulerHelper::canDo('core.edit.all')) {
                $url = JRoute::_("index.php?option={$this->option}&amp;task=task.edit&amp;id={$item->id}&amp;return={$return}");
                $arr['edit_link'] = JHtml::link($url, JText::sprintf('COM_MKV_HEAD_OPEN'));
            }
            $result['items'][$item->status][] = $arr;
        }
        if ($this->dat !== null) return ['cnt' => (int) (count($result['items'][-2]) + count($result['items'][1]) + count($result['items'][2]))];
        krsort($result['items'][3]);
        return $result;
    }

    public function export()
    {
        $items = $this->getItems();
        JLoader::discover('PHPExcel', JPATH_LIBRARIES);
        JLoader::register('PHPExcel', JPATH_LIBRARIES . '/PHPExcel.php');

        $xls = new PHPExcel();
        $xls->setActiveSheetIndex(0);
        $sheet = $xls->getActiveSheet();

        //Ширина столбцов
        $width = ["A" => 20, "B" => 13, "C" => 22, "D" => 80, "E" => 80, "F" => 80, "H" => 18];
        foreach ($width as $col => $value) $sheet->getColumnDimension($col)->setWidth($value);
        //Заголовки
        $j = 0;
        foreach ($this->heads as $item => $head) $sheet->setCellValueByColumnAndRow($j++, 1, JText::sprintf($head));

        $sheet->setTitle(JText::sprintf('COM_SCHEDULER_MENU_TASKS'));

        //Данные
        $row = 2; //Строка, с которой начнаются данные
        $col = 0;
        $arr = [-2, 1, 2, 3];
        foreach ($arr as $status) {
            foreach ($items['items'][$status] as $i => $item) {
                foreach ($this->heads as $elem => $head) {
                    $sheet->setCellValueByColumnAndRow($col++, $row, $item[$elem]);
                }
                $col = 0;
                $row++;
            }
        }
        header("Expires: Mon, 1 Apr 1974 05:00:00 GMT");
        header("Last-Modified: " . gmdate("D,d M YH:i:s") . " GMT");
        header("Cache-Control: no-cache, must-revalidate");
        header("Pragma: public");
        header("Content-type: application/vnd.ms-excel");
        header("Content-Disposition: attachment; filename=Tasks.xls");
        $objWriter = PHPExcel_IOFactory::createWriter($xls, 'Excel5');
        $objWriter->save('php://output');
        jexit();
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
        $date_1 = $this->getUserStateFromRequest($this->context . '.filter.date_1', 'filter_date_1');
        $this->setState('filter.date_1', $date_1);
        $date_2 = $this->getUserStateFromRequest($this->context . '.filter.date_2', 'filter_date_2');
        $this->setState('filter.date_2', $date_2);
        parent::populateState($ordering, $direction);
        PrjHelper::check_refresh();
    }

    protected function getStoreId($id = '')
    {
        $id .= ':' . $this->getState('filter.search');
        $id .= ':' . $this->getState('filter.manager');
        $id .= ':' . $this->getState('filter.status');
        $id .= ':' . $this->getState('filter.date_1');
        $id .= ':' . $this->getState('filter.date_2');
        return parent::getStoreId($id);
    }

    private $export, $contractID, $dat, $heads;
}
