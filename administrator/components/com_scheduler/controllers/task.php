<?php
defined('_JEXEC') or die;
use Joomla\CMS\MVC\Controller\FormController;

class SchedulerControllerTask extends FormController {

    public function add()
    {
        $uri = JUri::getInstance();
        $contractID = $uri->getVar('contractID', 0);
        $referer = JUri::getInstance($_SERVER['HTTP_REFERER']);
        if ($referer->getVar('view') === 'contract') {
            $contractID = $referer->getVar('id');
            $this->input->set('return', base64_encode($_SERVER['HTTP_REFERER']));
        }
        if ($referer->getVar('view') === 'task') {
            $contractID = $referer->getVar('contractID');
            $this->input->set('return', base64_encode($referer->getVar('return')));
        }
        if ($referer->getVar('view') === 'tasks') {
            $this->input->set('return', base64_encode($referer->toString()));
        }
        if ($contractID > 0) JFactory::getApplication()->setUserState($this->option . '.task.contractID', $contractID);
        return parent::add();
    }

    public function edit($key = null, $urlVar = null)
    {
        $uri = JUri::getInstance();
        $id = $uri->getVar('id', 0);
        if ($id > 0) {
            $model = $this->getModel();
            $item = $model->getItem($id);
            JFactory::getApplication()->setUserState($this->option . '.task.contractID', $item->contractID);
        }
        return parent::edit($key, $urlVar);
    }

    public function gotoContractActiveTask()
    {
        $uri = JUri::getInstance();
        $contractID = $uri->getVar('contractID', 0);
        $return = $uri->getVar('return');
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $query
            ->select("id")
            ->from("#__mkv_scheduler")
            ->where("status <> 3")
            ->where("contractID = {$db->q($contractID)}");
        $result = $db->setQuery($query)->loadResult();
        if (!is_numeric($result)) {
            $this->setRedirect(base64_decode($uri->getVar('return')));
            $this->redirect();
            jexit();
        }
        $this->setRedirect("index.php?option={$this->option}&task=task.edit&id={$result}&return={$return}");
        $this->redirect();
        jexit();
    }

    public function updateTask()
    {
        $app = JFactory::getApplication();
        $uri = JUri::getInstance($_SERVER['HTTP_REFERER']);
        $result = urldecode($app->input->getString('result'));
        if (empty($result)) {
            $type = 'error';
            $msg = JText::sprintf('COM_SCHEDULER_ERROR_EMPTY_RESULT');
        }
        else {
            $model = $this->getModel();
            $taskID = $app->input->getInt('id', 0);
            $item = $model->getItem($taskID);
            if ($item->id == null) {
                $type = 'error';
                $msg = JText::sprintf('COM_SCHEDULER_ERROR_BAD_TASK_ID');
            }
            else {
                if (!SchedulerHelper::canDo('core.edit.all') && $item->managerID != JFactory::getUser()->id) {
                    $type = 'error';
                    $msg = JText::sprintf('COM_SCHEDULER_ERROR_BAD_MANAGER_ID');
                } else {
                    if ((int)$item->status === 3) {
                        $type = 'error';
                        $msg = JText::sprintf('COM_SCHEDULER_ERROR_TASK_IS_CLOSED');
                    }
                    else {
                        $arr = [];
                        $arr['id'] = $taskID;
                        $arr['result'] = $result;
                        if (!$model->save($arr)) {
                            $type = 'error';
                            $msg = $model->getError();
                        }
                        else {
                            $type = 'message';
                            $msg = JText::sprintf('COM_SCHEDULER_MSG_RESULT_SAVE');
                        }
                    }
                }
            }
        }
        $app->enqueueMessage($msg, $type);
        $app->redirect($uri);
        jexit();
    }

    public function getModel($name = 'Task', $prefix = 'SchedulerModel', $config = array('ignore_request' => true))
    {
        return parent::getModel($name, $prefix, $config);
    }

    public function display($cachable = false, $urlparams = array())
    {
        return parent::display($cachable, $urlparams);
    }

    public function __construct($config = array())
    {
        $this->registerTask('save2new', 'save');
        parent::__construct($config);
    }
}