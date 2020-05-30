<?php
use Joomla\CMS\MVC\View\HtmlView;

defined('_JEXEC') or die;

class SchedulerViewTasks extends HtmlView
{
    protected $sidebar = '';
    public $items, $pagination, $uid, $state, $filterForm, $activeFilters, $contractID;

    public function display($tpl = null)
    {
        $this->items = $this->get('Items');
        $this->pagination = $this->get('Pagination');
        $this->state = $this->get('State');
        $this->filterForm = $this->get('FilterForm');
        $this->activeFilters = $this->get('ActiveFilters');
        $this->contractID = $this->get('ContractID');

        $this->filterForm->addFieldPath(JPATH_ADMINISTRATOR . "/components/com_mkv/models/fields");

        // Show the toolbar
        $this->toolbar();

        // Show the sidebar
        SchedulerHelper::addSubmenu('tasks');
        $this->sidebar = JHtmlSidebar::render();

        // Display it all
        return parent::display($tpl);
    }

    private function toolbar()
    {
        $title = $this->get('Title');
        JToolBarHelper::title($title, 'clock');
        if ($this->contractID > 0) {
            JToolbarHelper::custom('tasks.reset', 'back', 'back', JText::sprintf('COM_SCHEDULER_BUTTON_RESET_CONTRACT'), false);
        }

        if (SchedulerHelper::canDo('core.create') && $this->contractID > 0)
        {
            JToolbarHelper::addNew('task.add');
        }
        if (SchedulerHelper::canDo('core.edit'))
        {
            JToolbarHelper::editList('task.edit');
        }
        if (SchedulerHelper::canDo('core.delete'))
        {
            JToolbarHelper::deleteList('COM_SCHEDULER_CONFIRM_REMOVE_TASK', 'tasks.delete');
        }
        if (SchedulerHelper::canDo('core.admin'))
        {
            JToolBarHelper::preferences('com_scheduler');
        }
    }
}
