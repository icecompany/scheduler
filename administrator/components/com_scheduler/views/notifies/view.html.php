<?php
use Joomla\CMS\MVC\View\HtmlView;

defined('_JEXEC') or die;

class SchedulerViewNotifies extends HtmlView
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
        $this->filterForm->setValue('manager', 'filter', $this->state->get('filter.manager'));
        $this->filterForm->setValue('status', 'filter', $this->state->get('filter.status'));
        if (!SchedulerHelper::canDo('core.edit.all')) {
            $this->filterForm->removeField('manager', 'filter');
            $this->filterForm->removeField('status', 'filter');
        }

        // Show the toolbar
        $this->toolbar();

        // Show the sidebar
        SchedulerHelper::addSubmenu('notifies');
        $this->sidebar = JHtmlSidebar::render();

        // Display it all
        return parent::display($tpl);
    }

    private function toolbar()
    {
        $title = $this->get('Title');
        JToolBarHelper::title($title, 'info');
        if ($this->contractID > 0) {
            JToolbarHelper::custom('notifies.reset', 'back', 'back', JText::sprintf('COM_SCHEDULER_BUTTON_RESET_NOTIFY'), false);
        }
        JToolbarHelper::custom('notifies.read', 'flag', 'flag', JText::sprintf('COM_SCHEDULER_BUTTON_READ'));
        if (SchedulerHelper::canDo('core.admin'))
        {
            JToolBarHelper::preferences('com_scheduler');
        }
    }
}
