<?php
use Joomla\CMS\MVC\Controller\AdminController;
use Joomla\Utilities\ArrayHelper;

defined('_JEXEC') or die;

class SchedulerControllerNotifies extends AdminController
{
    public function getModel($name = 'Notify', $prefix = 'SchedulerModel', $config = array())
    {
        return parent::getModel($name, $prefix, $config);
    }

    public function reset()
    {
        $this->setRedirect("index.php?option=com_scheduler&view=notifies");
        $this->redirect();
        jexit();
    }

    public function read()
    {
        $this->checkToken();
        $cid = $this->input->get('cid', array(), 'array');
        if (!is_array($cid) || count($cid) < 1)
        {
            \JLog::add(\JText::_($this->text_prefix . '_NO_ITEM_SELECTED'), \JLog::WARNING, 'jerror');
        }
        else
        {
            $model = $this->getModel();
            foreach ($cid as $id) {
                $item = $model->getItem($id);
                $date_read = JDate::getInstance("now + 1 week")->toSql();
                $model->save(['id' => $item->id, 'date_read' => $date_read, 'status' => 1]);
            }
        }

        $this->setRedirect($_SERVER['HTTP_REFERER']);
        $this->redirect();
        jexit();
    }
}
