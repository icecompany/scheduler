<?php
defined('_JEXEC') or die;
use Joomla\CMS\MVC\View\HtmlView;

class SchedulerViewTask extends HtmlView {
    protected $item, $form, $script, $contacts, $history, $tasks, $version, $versionObject;

    public function display($tmp = null) {
        $this->form = $this->get('Form');
        $this->item = $this->get('Item');
        $this->script = $this->get('Script');
        $this->contacts = $this->get('Contacts');
        $this->history = $this->get('History');
        $this->version = $this->get('Version');
        $this->versionObject = $this->get('VersionObject');
        $this->tasks = $this->get('Tasks');

        if ($this->item->id !== null) {
            $this->form->removeField('template_task');
            //Снимаем атрибут только для чтения для встречи
            $this->form->setFieldAttribute('type', 'readonly', true);
            if ($this->item->type === 'meet') {
                $this->form->setFieldAttribute('contactID', 'readonly', false);
                $this->form->setFieldAttribute('theme', 'readonly', false);
                $this->form->setFieldAttribute('place', 'readonly', false);
            }
        }

        $this->addToolbar();
        $this->setDocument();

        parent::display($tmp);
    }

    protected function addToolbar() {
        if ($this->version === 0) {
            JToolBarHelper::apply('task.apply', 'JTOOLBAR_APPLY');
            JToolbarHelper::save('task.save', 'JTOOLBAR_SAVE');
            JToolbarHelper::save2new('task.save2new');
        }
        else {
            JToolbarHelper::custom('task.gotoActualVersion', 'back', 'back', JText::sprintf('COM_MKV_BUTTON_GOTO_ACTUAL_VERSION'), false);
        }
        JToolbarHelper::cancel('task.cancel', 'JTOOLBAR_CLOSE');
        JFactory::getApplication()->input->set('hidemainmenu', true);
    }

    protected function setDocument() {
        JToolbarHelper::title($this->item->title, 'calendar');
        JHtml::_('bootstrap.framework');
    }
}