<?php
use Joomla\CMS\MVC\View\HtmlView;

defined('_JEXEC') or die;

class SchedulerViewTemplates extends HtmlView
{
    protected $sidebar = '';
    public $items, $pagination, $uid, $state, $filterForm, $activeFilters;

    public function display($tpl = null)
    {
        $this->items = $this->get('Items');
        $this->pagination = $this->get('Pagination');
        $this->state = $this->get('State');
        $this->filterForm = $this->get('FilterForm');
        $this->activeFilters = $this->get('ActiveFilters');

        // Show the toolbar
        $this->toolbar();

        // Show the sidebar
        SchedulerHelper::addSubmenu('templates');
        $this->sidebar = JHtmlSidebar::render();

        // Display it all
        return parent::display($tpl);
    }

    private function toolbar()
    {
        JToolBarHelper::title(JText::sprintf('COM_SCHEDULER_MENU_TEMPLATES'), 'copy');
        if (SchedulerHelper::canDo('core.create'))
        {
            JToolbarHelper::addNew('template.add');
        }
        if (SchedulerHelper::canDo('core.edit'))
        {
            JToolbarHelper::editList('template.edit');
        }
        if (SchedulerHelper::canDo('core.delete'))
        {
            JToolbarHelper::deleteList('COM_SCHEDULER_CONFIRM_REMOVE_TEMPLATE', 'templates.delete');
        }
        if (SchedulerHelper::canDo('core.admin'))
        {
            JToolBarHelper::preferences('com_scheduler');
        }
    }
}
