<?php
use Joomla\CMS\MVC\Controller\AdminController;

defined('_JEXEC') or die;

class SchedulerControllerTasks extends AdminController
{
    public function execute($task): void
    {
        $model = $this->getModel();
        $model->export();
        jexit();
    }

    public function getModel($name = 'Tasks', $prefix = 'SchedulerModel', $config = array())
    {
        return parent::getModel($name, $prefix, $config);
    }
}
