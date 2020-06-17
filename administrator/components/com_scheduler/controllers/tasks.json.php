<?php
defined('_JEXEC') or die;
use Joomla\CMS\Response\JsonResponse;
use Joomla\CMS\MVC\Controller\BaseController;

class SchedulerControllerTasks extends BaseController
{
    public function execute($task)
    {
        $dat = $this->input->getString('date', null);
        if ($dat !== null) {
            $items = $this->getModel($name = 'Tasks', $prefix = 'SchedulerModel', $config = ['dat' => $dat])->getItems();
            echo new JsonResponse($items);
        }
    }
}