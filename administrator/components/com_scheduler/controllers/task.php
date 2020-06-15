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
        if ($contractID > 0) JFactory::getApplication()->setUserState($this->option . '.task.contractID', $contractID);
        return parent::add();
    }

    public function gotoContractActiveTask()
    {

    }

    public function display($cachable = false, $urlparams = array())
    {
        return parent::display($cachable, $urlparams);
    }
}