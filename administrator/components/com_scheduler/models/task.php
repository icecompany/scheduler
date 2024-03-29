<?php
defined('_JEXEC') or die;
use Joomla\CMS\MVC\Model\AdminModel;
use Joomla\CMS\MVC\Model\ListModel;

class SchedulerModelTask extends AdminModel {
    public function __construct($config = array())
    {
        $this->version = JFactory::getApplication()->input->get('version', 0);
        parent::__construct($config);
    }

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
            $item->companyID = $contract->companyID;
            $item->contractID_final = $item->contractID;
            //Подгружаем встречу
            $meeting = $this->loadMeeting($item->id);
            if (!empty($meeting->theme)) {
                $item->taskID = $item->id;
                $item->contactID = $meeting->contactID;
                $item->place = $meeting->place;
                $item->theme = $meeting->theme;
                $item->type = 'meet';
            }
        }
        return $item;
    }

    public function save($data)
    {
        $app = JFactory::getApplication();
        if ($data['type'] === 'meet') {
            if (empty($data['theme'])) {
                $app->enqueueMessage(JText::sprintf('COM_SCHEDULER_ERROR_EMPTY_THEME'), 'warning');
                return false;
            }
            if (empty($data['place'])) {
                $app->enqueueMessage(JText::sprintf('COM_SCHEDULER_ERROR_EMPTY_PLACE'), 'warning');
                return false;
            }
        }

        $data['date_task'] = JDate::getInstance($data['date_task'])->format("Y-m-d");
        if ($data['id'] !== null) {
            $item = parent::getItem($data['id']);
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
        //Сохраняем встречу
        if ($s && $data['type'] === 'meet') {
            $event = [];
            $event['taskID'] = $data['id'] ?? JFactory::getDbo()->insertid();
            $event['contactID'] = $data['contactID'];
            $event['place'] = $data['place'];
            $event['theme'] = $data['theme'];
            if (!$this->saveMeeting($event)) {
                $app->enqueueMessage(JText::sprintf('COM_SCHEDULER_ERROR_NOT_SAVE_EVENT'), 'error');
                return false;
            }
        }
        //Пишем в историю
        if ($s) {
            $hst = [];
            $hst['managerID'] = JFactory::getUser()->id;
            $hst['itemID'] = $data['id'] ?? JFactory::getDbo()->insertid();
            $hst['action'] = ($data['id'] !== null) ? 'update' : 'add';
            $hst['section'] = 'task';
            $hst['new_data'] = json_encode($data);
            $hst['old_data'] = '';
            if ($hst['action'] === 'update' && !empty($item)) {
                $hst['old_data'] = json_encode($item ?? []);
            }
            JTable::addIncludePath(JPATH_ADMINISTRATOR . "/components/com_mkv/tables");
            $history = JTable::getInstance('History', 'TableMkv');
            $history->save($hst);
        }

        //Уведомление в случае переноса задачи на более позднюю дату
        if (!empty($item) && !empty($data) && $this->isNeedNotify($item, $data)) {
            $this->sendNewTaskDate($data ?? [], $item->date_task);
        }

        return $s;
    }

    public function getHistory(): array
    {
        $item = parent::getItem();
        if ($item->id === null) return [];
        JModelLegacy::addIncludePath(JPATH_ADMINISTRATOR . "/components/com_mkv/models", "MkvModel");
        $model = JModelLegacy::getInstance("Events", "MkvModel", ['section' => 'task', 'itemID' => $item->id]);
        return $model->getItems() ?? [];
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
        $form->addFieldPath(JPATH_ADMINISTRATOR . "/components/com_mkv/models/fields");
        $form->addFieldPath(JPATH_ADMINISTRATOR . "/components/com_companies/models/fields");

        if ($this->version > 0) {
            $form->setFieldAttribute('date_task', 'readonly', true);
            $form->setFieldAttribute('managerID', 'readonly', true);
            $form->setFieldAttribute('template_task', 'readonly', true);
            $form->setFieldAttribute('task', 'readonly', true);
            $form->setFieldAttribute('template_result', 'readonly', true);
            $form->setFieldAttribute('result', 'readonly', true);
        }

        return $form;
    }

    protected function loadFormData()
    {
        $data = JFactory::getApplication()->getUserState($this->option.'.edit.task.data', array());
        if (empty($data))
        {
            $data = $this->getItem();
        }
        if ($this->version > 0) {
            $old = $this->getVersionObject();
            $data = json_decode($old->new_data);
        }

        return $data;
    }

    public function getVersion() {
        return $this->version;
    }

    public function getVersionObject()
    {
        JTable::addIncludePath(JPATH_ADMINISTRATOR . "/components/com_mkv/tables");
        $table = JTable::getInstance("History", "TableMkv");
        $table->load($this->version);
        return $table;
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

    private function sendNewTaskDate(array $data, $date_old): void
    {
        if (!$this->isNotifyTaskDateEnabled()) return;

        if (empty($users = $this->getNotifyUserGroup())) return;

        jimport('joomla.mail.helper');
        foreach ($users as $userID) {
            $user = JFactory::getUser($userID);
            $mailer = MkvHelper::getMailer();
            $mailer->addRecipient($user->email, $user->name);
            $mailer->setSubject(JText::sprintf('COM_SCHEDULER_EMAIL_NEW_TASK_DATA_TITLE'));
            $mailer->setBody($this->getNewTaskNotifyText($data, $date_old));
            $mailer->Send();
        }
    }

    private function isNeedNotify($old_data, $new_data): bool
    {
        if (!empty($old_data->result) || !empty($new_data['result'])) return false;

        $date_old = JDate::getInstance($old_data->date_task);
        $date_new = JDate::getInstance($new_data['date_task']);
        $date_old->setTime(0, 0, 0, 0);
        $date_new->setTime(0, 0, 0, 0);

        return ($date_new > $date_old);
    }

    private function getNewTaskNotifyText($data, $date_old): string
    {
        $manager = JFactory::getUser()->name;
        $company = $this->getContract($data['contractID'])->company;
        $task_link = $this->getTaskLink($data['id'], $company);

        $date_old = JDate::getInstance($date_old);
        $date_new = JDate::getInstance($data['date_task']);
        $task_text = JText::sprintf('COM_SCHEDULER_EMAIL_NEW_TASK_DATA_TASK_TEXT', $data['task']);

        $text = "<p>" . JText::sprintf('COM_SCHEDULER_EMAIL_NEW_TASK_DATA_TEXT', $manager, $task_link) . "</p><br>";
        $text .= "<p>";
        $text .= JText::sprintf('COM_SCHEDULER_EMAIL_NEW_TASK_DATA_TEXT_OLD_DATE', $date_old->format("d.m.Y"));
        $text .= "<br>";
        $text .= JText::sprintf('COM_SCHEDULER_EMAIL_NEW_TASK_DATA_TEXT_NEW_DATE', $date_new->format("d.m.Y"), $date_new->diff($date_old)->days);
        $text .= "</p><br>";
        $text .= "<p>{$task_text}</p>";

        return $text;
    }

    public function getTasks(): array
    {
        $item = parent::getItem();
        if (!is_numeric($item->contractID)) return [];
        $model = ListModel::getInstance('Tasks', 'SchedulerModel', ['contractID' => $item->contractID]);
        return $model->getItems();
    }

    private function getTaskLink(int $taskID, $company): string
    {
        $url = JRoute::_("index.php?option=com_scheduler&amp;task=task.edit&amp;id={$taskID}");
        $url = "https://port.icecompany.org/{$url}";
        return JHtml::link($url, JText::sprintf('COM_SCHEDULER_EMAIL_NEW_TASK_DATA_TASK_LINK_TEXT', $company));
    }

    private function isNotifyTaskDateEnabled(): bool
    {
        return (SchedulerHelper::getConfig('notify_new_task_date_enabled') == '1');
    }

    private function getNotifyUserGroup()
    {
        $group_id = SchedulerHelper::getConfig('notify_new_task_date_user_group');
        if (!is_numeric($group_id)) return false;
        return MkvHelper::getGroupUsers($group_id);
    }

    private function saveMeeting(array $data): bool
    {
        $table = JTable::getInstance('Meetings', 'TableScheduler', []);
        $table->load(['taskID' => $data['taskID']]);
        $data['id'] = $table->id;
        return $table->save($data);
    }

    private function loadMeeting(int $taskID)
    {
        if ($taskID < 1) return [];
        $table = JTable::getInstance('Meetings', 'TableScheduler', []);
        $table->load(['taskID' => $taskID]);
        return $table;
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

    private $version;
}