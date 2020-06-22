<?php
use Joomla\CMS\MVC\Controller\AdminController;
use Joomla\Utilities\ArrayHelper;

defined('_JEXEC') or die;

class SchedulerControllerTasks extends AdminController
{
    public function download(): void
    {
        echo "<script>window.open('index.php?option=com_scheduler&task=tasks.execute&format=xls');</script>";
        echo "<script>location.href='{$_SERVER['HTTP_REFERER']}'</script>";
        jexit();
    }

    public function getModel($name = 'Task', $prefix = 'SchedulerModel', $config = array())
    {
        return parent::getModel($name, $prefix, $config);
    }

    public function reset()
    {
        $this->setRedirect("index.php?option=com_scheduler&view=tasks");
        $this->redirect();
        jexit();
    }

    public function delete()
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
            $cid = ArrayHelper::toInteger($cid);

            if ($model->delete($cid))
            {
                $this->setMessage(\JText::plural($this->text_prefix . '_N_ITEMS_DELETED', count($cid)));
            }
            else
            {
                $this->setMessage($model->getError(), 'error');
            }

            $this->postDeleteHook($model, $cid);
        }

        $this->setRedirect($_SERVER['HTTP_REFERER']);
        $this->redirect();
        jexit();
    }

    public function plus_one_week()
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
                $date_task = JDate::getInstance("now + 1 week")->toSql();
                $model->save(['id' => $item->id, 'date_task' => $date_task]);
            }
        }

        $this->setRedirect($_SERVER['HTTP_REFERER']);
        $this->redirect();
        jexit();
    }

    public function __construct($config = array())
    {
        $this->registerTask('save2new', 'save');
        parent::__construct($config);
    }
}
