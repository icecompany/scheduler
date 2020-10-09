<?php
defined('_JEXEC') or die;
use Joomla\CMS\MVC\Model\AdminModel;

class SchedulerModelTask extends AdminModel {

    public function getItem($pk = null)
    {
        $item = parent::getItem($pk);
        if ($item->id === null) {
            $item->contractID = JFactory::getApplication()->getUserState($this->option.'.task.contractID');
            $item->date_task = JDate::getInstance('now + 1 weekday')->toSql();
        }
        $contract = $this->getContract($item->contractID);
        if ($item->id === null) {
            $item->managerID = $contract->managerID;
            $item->title = JText::sprintf('COM_SCHEDULER_TITLE_TASK_ADD', $contract->company, $contract->project);
        }
        else {
            $item->title = JText::sprintf('COM_SCHEDULER_TITLE_TASK_EDIT', $contract->company, $contract->project);
        }
        return $item;
    }

    public function save($data)
    {
        $data['date_task'] = JDate::getInstance($data['date_task'])->format("Y-m-d");
        if ($data['id'] !== null) {
            if (!empty($data['result'])) {
                $data['date_close'] = JDate::getInstance()->toSql();
                $data['user_close'] = JFactory::getUser()->id;
                $data['status'] = 3;
                if (JDate::getInstance($data['date_task']) > JDate::getInstance()) {
                    $data['date_task'] = JDate::getInstance()->toSql();
                }
            }
            else {
                if (JDate::getInstance($data['date_task']) == JDate::getInstance()->setTime(0, 0, 0)) {
                    $data['status'] = 1;
                }
                if (JDate::getInstance($data['date_task']) > JDate::getInstance()->setTime(0, 0, 0)) {
                    $data['status'] = 2;
                }
            }
        }
        else {
            if (!empty($data['result'])) {
                $data['date_close'] = JDate::getInstance()->toSql();
                $data['user_close'] = JFactory::getUser()->id;
                $data['user_open'] = JFactory::getUser()->id;
                $data['status'] = 3;
            }
            else {
                $data['date_create'] = JDate::getInstance()->toSql();
                $data['user_open'] = JFactory::getUser()->id;
                if (JDate::getInstance($data['date_task']) == JDate::getInstance()->setTime(0, 0, 0)) {
                    $data['status'] = 1;
                }
                if (JDate::getInstance($data['date_task']) > JDate::getInstance()->setTime(0, 0, 0)) {
                    $data['status'] = 2;
                }
            }
        }
        $s = parent::save($data);
        //Пишем в историю
        if ($s) {
            $hst = [];
            $hst['managerID'] = JFactory::getUser()->id;
            $hst['itemID'] = $data['id'] ?? JFactory::getDbo()->insertid();
            $hst['action'] = ($data['id'] !== null) ? 'update' : 'add';
            $hst['section'] = 'task';
            $hst['new_data'] = json_encode($data);
            $hst['old_data'] = '';
            if ($hst['action'] === 'update') {
                $item = parent::getItem($data['id']);
                $hst['old_data'] = json_encode($item);
            }
            JTable::addIncludePath(JPATH_ADMINISTRATOR . "/components/com_mkv/tables");
            $history = JTable::getInstance('History', 'TableMkv');
            $history->save($hst);
        }
        return $s;
    }

    public function getContacts()
    {
        $item = parent::getItem();
        $contract = $this->getContract($item->contractID ?? JFactory::getApplication()->getUserState($this->option . ".task.contractID"));
        JModelLegacy::addIncludePath(JPATH_ADMINISTRATOR . "/components/com_companies/models", "CompaniesModel");
        JTable::addIncludePath(JPATH_ADMINISTRATOR . "/components/com_companies/tables");
        $model = JModelLegacy::getInstance("Contacts", "CompaniesModel", ['companyID' => $contract->companyID]);
        return $model->getItems();
    }

    public function getTable($name = 'Scheduler', $prefix = 'TableScheduler', $options = array())
    {
        return JTable::getInstance($name, $prefix, $options);
    }

    public function getForm($data = array(), $loadData = true)
    {
        $form = $this->loadForm(
            $this->option.'.task', 'task', array('control' => 'jform', 'load_data' => $loadData)
        );
        if (empty($form))
        {
            return false;
        }
        $form->addFieldPath(JPATH_ADMINISTRATOR."/components/com_mkv/models/fields");

        return $form;
    }

    protected function loadFormData()
    {
        $data = JFactory::getApplication()->getUserState($this->option.'.edit.task.data', array());
        if (empty($data))
        {
            $data = $this->getItem();
        }

        return $data;
    }

    protected function prepareTable($table)
    {
        $all = get_class_vars($table);
        unset($all['_errors']);
        $nulls = ['date_close', 'user_close', 'result']; //Поля, которые NULL
        foreach ($all as $field => $v) {
            if (empty($field)) continue;
            if (in_array($field, $nulls)) {
                if (!strlen($table->$field)) {
                    $table->$field = NULL;
                    continue;
                }
            }
            if (!empty($field)) $table->$field = trim($table->$field);
        }

        parent::prepareTable($table);
    }

    private function getContract(int $contractID)
    {
        JModelLegacy::addIncludePath(JPATH_ADMINISTRATOR . "/components/com_contracts/models");
        JTable::addIncludePath(JPATH_ADMINISTRATOR . "/components/com_contracts/tables");
        $model = JModelLegacy::getInstance('Contract', 'ContractsModel');
        return $model->getItem($contractID);
    }

    public function delete(&$pks)
    {
        //Пишем историю
        JTable::addIncludePath(JPATH_ADMINISTRATOR . "/components/com_mkv/tables");
        foreach ($pks as $pk) {
            $item = parent::getItem($pk);
            $d = parent::delete($pk);
            if ($d) {
                $hst = [];
                $hst['managerID'] = JFactory::getUser()->id;
                $hst['itemID'] = $item->id;
                $hst['section'] = 'task';
                $hst['action'] = 'delete';
                $hst['old_data'] = json_encode($item);
                $hst['new_data'] = '';
                $history = JTable::getInstance('History', 'TableMkv');
                $history->save($hst);
            }
            else return false;
        }
        return true;
    }

    protected function canEditState($record)
    {
        $user = JFactory::getUser();

        if (!empty($record->id))
        {
            return $user->authorise('core.edit.state', $this->option . '.task.' . (int) $record->id);
        }
        else
        {
            return parent::canEditState($record);
        }
    }

    public function getScript()
    {
        return 'administrator/components/' . $this->option . '/models/forms/task.js';
    }
}