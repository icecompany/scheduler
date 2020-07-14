<?php
defined('_JEXEC') or die;
use Joomla\CMS\MVC\Controller\FormController;

class SchedulerControllerNotify extends FormController {
    public function cancel($key = null)
    {
        $app = JFactory::getApplication();
        $id = $app->input->getInt('id', 0);
        JTable::addIncludePath(JPATH_COMPONENT_ADMINISTRATOR . "/tables");
        $table = JTable::getInstance('Notifies', 'TableScheduler');
        $table->load($id);
        $data['id'] = $id;
        $data['date_read'] = JDate::getInstance()->toSql();
        $data['user_create'] = JFactory::getUser()->id;
        $data['status'] = 1;
        $table->save($data);
        $query = [];
        $query['option'] = 'com_contracts';
        if ($table->contractID !== null) {
            $query['task'] = 'contract.edit';
            $query['id'] = $table->contractID;
        }
        else {
            $query['view'] = 'contracts';
        }
        $app->redirect("index.php?" . http_build_query($query));
        jexit();
    }

    public function display($cachable = false, $urlparams = array())
    {
        return parent::display($cachable, $urlparams);
    }
}