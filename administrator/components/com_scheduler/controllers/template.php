<?php
defined('_JEXEC') or die;
use Joomla\CMS\MVC\Controller\FormController;

class SchedulerControllerTemplate extends FormController {
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