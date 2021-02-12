<?php
use Joomla\CMS\MVC\Controller\AdminController;

defined('_JEXEC') or die;

class SchedulerControllerNotifies extends AdminController
{
    public function execute($task): void
    {
        $model = $this->getModel();
        $model->export();
        jexit();
    }

    public function getModel($name = 'Notifies', $prefix = 'SchedulerModel', $config = array())
    {
        return parent::getModel($name, $prefix, $config);
    }
}
