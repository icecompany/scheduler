<?php
use Joomla\CMS\MVC\Model\ListModel;

defined('_JEXEC') or die;

class SchedulerModelNotifies extends ListModel
{
    public function __construct($config = array())
    {
        if (empty($config['filter_fields'])) {
            $config['filter_fields'] = array(
                'n.id',
                'n.date_create',
                'n.status, n.date_create',
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
        $this->heads = [
            'status' => 'COM_MKV_HEAD_STATUS',
            'date_create' => 'COM_MKV_HEAD_DATE',
            'contract_status' => 'COM_MKV_HEAD_CONTRACT_STATUS',
            'company' => 'COM_MKV_HEAD_COMPANY',
            'text' => 'COM_SCHEDULER_HEAD_NOTIFY_TEXT',
            'manager' => 'COM_MKV_HEAD_MANAGER',
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

        $query
            ->select("n.*")
            ->select("c.companyID, ifnull(c.number_free, c.number) as contract_number, c.status as contract_status_code")
            ->select("cs.title as contract_status")
            ->select("e.title as company")
            ->select("u.name as manager")
            ->from("#__mkv_notifies n")
            ->leftJoin("#__mkv_contracts c on c.id = n.contractID")
            ->leftJoin("#__mkv_contract_statuses cs on cs.code = c.status")
            ->leftJoin("#__mkv_companies e on e.id = c.companyID")
            ->leftJoin("#__users u on u.id = n.managerID");

        $project = PrjHelper::getActiveProject();
        if (is_numeric($project)) {
            $query->where("c.projectID = {$this->_db->q($project)}");
        }
        $status = $this->getState('filter.status');
        if (is_numeric($status)) {
            $query->where("n.status = {$this->_db->q($status)}");
        }

        $search = $this->getState('filter.search');
        if ($this->contractID === null) {
            if (!empty($search)) {
                if (stripos($search, 'id:') !== false) { //Поиск по ID
                    $id = explode(':', $search);
                    $id = $id[1];
                    if (is_numeric($id)) {
                        $query->where("n.id = {$this->_db->q($id)}");
                    }
                } else {
                    $text = $this->_db->q("%{$search}%");
                    $query->where("(e.title like {$text} or n.text like {$text})");
                }
            }
            $manager = $this->getState('filter.manager');
            if (is_numeric($manager)) {
                $query->where("n.managerID = {$this->_db->q($manager)}");
            }
        }
        else {
            $query->where("n.contractID = {$this->_db->q($this->contractID)}");
            $limit = 0;
        }

        $query->order($this->_db->escape($orderCol . ' ' . $orderDirn));
        $this->setState('list.limit', $limit);

        return $query;
    }

    public function getItems()
    {
        $items = parent::getItems();
        $result = ['items' => []];
        $return = PrjHelper::getReturnUrl();
        foreach ($items as $item) {
            $arr = [];
            $arr['id'] = $item->id;
            $arr['company'] = $item->company;
            $arr['date_create'] = JDate::getInstance($item->date_create)->format("d.m.Y");
            $arr['date_read'] = (!empty($item->date_read)) ? JDate::getInstance($item->date_read)->format("d.m.Y") : '';
            $arr['manager'] = MkvHelper::getLastAndFirstNames($item->manager);
            $arr['status'] = JText::sprintf("COM_SCHEDULER_NOTIFY_STATUS_{$item->status}");
            $arr['contract_status'] = $item->contract_status ?? JText::sprintf("COM_MKV_STATUS_IN_PROJECT");
            if ($item->contract_status_code == 1 && !empty($item->contract_number)) $arr['contract_status'] = JText::sprintf('COM_MKV_CONTRACT_TITLE_NUMBER', $item->contract_number);
            $url = JRoute::_("index.php?option=com_contracts&amp;task=contract.edit&amp;id={$item->contractID}&amp;return={$return}");
            $arr['contract_link'] = JHtml::link($url, $arr['contract_status']);
            $arr['text'] = $item->text;
            $url = JRoute::_("index.php?option={$this->option}&amp;view=tasks&amp;contractID={$item->contractID}");
            $arr['tasks_link'] = JHtml::link($url, $item->task);
            if ($this->contractID > 0) $arr['tasks_link'] = $item->task;
            $url = JRoute::_("index.php?option=com_companies&amp;task=company.edit&amp;id={$item->companyID}&amp;return={$return}");
            $arr['company_link'] = JHtml::link($url, $item->company);
            $url = JRoute::_("index.php?option={$this->option}&amp;task=notify.edit&amp;id={$item->id}&amp;return={$return}");
            $arr['edit_link'] = JHtml::link($url, JText::sprintf('COM_MKV_HEAD_OPEN'));
            $result['items'][] = $arr;
        }
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
        $width = ["A" => 20, "B" => 13, "C" => 22, "D" => 80, "E" => 80, "F" => 22];
        foreach ($width as $col => $value) $sheet->getColumnDimension($col)->setWidth($value);
        //Заголовки
        $j = 0;
        foreach ($this->heads as $item => $head) $sheet->setCellValueByColumnAndRow($j++, 1, JText::sprintf($head));

        $sheet->setTitle(JText::sprintf('COM_SCHEDULER_TITLE_NOTIFIES'));

        //Данные
        $row = 2; //Строка, с которой начнаются данные
        $col = 0;
        foreach ($items['items'] as $i => $item) {
            foreach ($this->heads as $elem => $head) {
                $sheet->setCellValueByColumnAndRow($col++, $row, $item[$elem]);
            }
            $col = 0;
            $row++;
        }
        header("Expires: Mon, 1 Apr 1974 05:00:00 GMT");
        header("Last-Modified: " . gmdate("D,d M YH:i:s") . " GMT");
        header("Cache-Control: no-cache, must-revalidate");
        header("Pragma: public");
        header("Content-type: application/vnd.ms-excel");
        header("Content-Disposition: attachment; filename=Notifies.xls");
        $objWriter = PHPExcel_IOFactory::createWriter($xls, 'Excel5');
        $objWriter->save('php://output');
        jexit();
    }

    public function getTitle(): string
    {
        if ($this->contractID > 0) {
            $contract = $this->getContract($this->contractID);
            return JText::sprintf('COM_SCHEDULER_TITLE_NOTIFIES_BY_CONTRACT', $contract->company, $contract->project);
        }
        else {
            return JText::sprintf('COM_SCHEDULER_TITLE_NOTIFIES');
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

    protected function populateState($ordering = 'n.status, n.date_create', $direction = 'desc')
    {
        $search = $this->getUserStateFromRequest($this->context . '.filter.search', 'filter_search');
        $this->setState('filter.search', $search);
        $manager = $this->getUserStateFromRequest($this->context . '.filter.manager', 'filter_manager', JFactory::getUser()->id);
        $this->setState('filter.manager', $manager);
        $status = $this->getUserStateFromRequest($this->context . '.filter.status', 'filter_status', 0);
        $this->setState('filter.status', $status);
        parent::populateState($ordering, $direction);
        PrjHelper::check_refresh();
    }

    protected function getStoreId($id = '')
    {
        $id .= ':' . $this->getState('filter.search');
        $id .= ':' . $this->getState('filter.manager');
        $id .= ':' . $this->getState('filter.status');
        return parent::getStoreId($id);
    }

    private $export, $contractID, $heads;
}
