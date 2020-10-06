<?php
defined('_JEXEC') or die;
use Joomla\CMS\MVC\View\HtmlView;

class SchedulerViewTask extends HtmlView {
    protected $item, $form, $script, $contacts;

    public function display($tmp = null) {
        $this->form = $this->get('Form');
        $this->item = $this->get('Item');
        $this->script = $this->get('Script');
        $this->contacts = $this->get('Contacts');

        if ($this->item->id !== null) {
            $this->form->removeField('template_task');
        }

        $this->addToolbar();
        $this->setDocument();

        parent::display($tmp);
    }

    protected function addToolbar() {
        JToolBarHelper::apply('task.apply', 'JTOOLBAR_APPLY');
        JToolbarHelper::save('task.save', 'JTOOLBAR_SAVE');
        JToolbarHelper::save2new('task.save2new');
        JToolbarHelper::cancel('task.cancel', 'JTOOLBAR_CLOSE');
        JFactory::getApplication()->input->set('hidemainmenu', true);
    }

    protected function setDocument() {
        JToolbarHelper::title($this->item->title, 'calendar');
        JHtml::_('bootstrap.framework');
    }
}