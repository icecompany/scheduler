<?php
defined('_JEXEC') or die;
jimport('joomla.form.helper');
JFormHelper::loadFieldClass('list');

class JFormFieldTemplateResult extends JFormFieldList
{
    protected $type = 'TemplateResult';
    protected $loadExternally = 0;
    private $tip = 'result';

    protected function getOptions()
    {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $managerID = JFactory::getUser()->id;
        $query
            ->select("t.id, t.title, t.text")
            ->from("`#__mkv_task_templates` t")
            ->where("t.managerID = {$db->q($managerID)}")
            ->where("t.type like {$db->q($this->tip)}")
            ->order("t.id desc");
        $result = $db->setQuery($query)->loadObjectList();

        $options = array();

        foreach ($result as $item) {
            $arr = array('data-text' => $item->text);
            $params = array('attr' => $arr, 'option.attr' => 'optionattr');
            $options[] = JHtml::_('select.option', $item->id, $item->title, $params);
        }

        if (!$this->loadExternally) {
            $options = array_merge(parent::getOptions(), $options);
        }

        return $options;
    }

    public function getOptionsExternally()
    {
        $this->loadExternally = 1;
        return $this->getOptions();
    }
}