<?php
defined('_JEXEC') or die;
use Joomla\CMS\MVC\View\HtmlView;

class SchedulerViewTemplate extends HtmlView {
    protected $item, $form, $script;

    public function display($tmp = null) {
        $this->form = $this->get('Form');
        $this->item = $this->get('Item');
        $this->script = $this->get('Script');

        $this->addToolbar();
        $this->setDocument();

        parent::display($tmp);
    }

    protected function addToolbar() {
        JToolBarHelper::apply('template.apply', 'JTOOLBAR_APPLY');
        JToolbarHelper::save('template.save', 'JTOOLBAR_SAVE');
        JToolbarHelper::save2new('template.save2new');
        JToolbarHelper::cancel('template.cancel', 'JTOOLBAR_CLOSE');
        JFactory::getApplication()->input->set('hidemainmenu', true);
    }

    protected function setDocument() {
        $title = ($this->item->id !== null) ? JText::sprintf('COM_SCHEDULER_TITLE_TEMPLATE_EDIT', $this->item->title) : JText::sprintf('COM_SCHEDULER_TITLE_TEMPLATE_ADD');
        JToolbarHelper::title($title, 'bookmark');
        JHtml::_('bootstrap.framework');
    }
}