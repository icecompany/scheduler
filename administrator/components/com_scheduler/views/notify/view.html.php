<?php
defined('_JEXEC') or die;
use Joomla\CMS\MVC\View\HtmlView;

class SchedulerViewNotify extends HtmlView {
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
        JToolbarHelper::cancel('notify.cancel', ($this->item->contractID !== null) ? 'COM_SCHEDULER_BUTTON_READ_AND_GO_TO_CONTRACT' : 'COM_SCHEDULER_BUTTON_READ_AND_GO_TO_CONTRACTS');
        JFactory::getApplication()->input->set('hidemainmenu', true);
    }

    protected function setDocument() {
        JToolbarHelper::title($this->item->title, 'notification');
        JHtml::_('bootstrap.framework');
    }
}