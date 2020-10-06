<?php
use Joomla\CMS\MVC\Model\ListModel;

defined('_JEXEC') or die;

class SchedulerModelTemplates extends ListModel
{
    public function __construct($config = array())
    {
        if (empty($config['filter_fields'])) {
            $config['filter_fields'] = array(
                't.id',
                't.type',
                'search',
            );
        }
        parent::__construct($config);
        $input = JFactory::getApplication()->input;
        $this->export = ($input->getString('format', 'html') === 'html') ? false : true;
    }

    protected function _getListQuery()
    {
        $query = $this->_db->getQuery(true);

        $query
            ->select("t.id, t.type, t.title, t.text")
            ->from("#__mkv_task_templates t");

        $search = $this->getState('filter.search');
        if (!empty($search)) {
            if (stripos($search, 'id:') !== false) { //Поиск по ID
                $id = explode(':', $search);
                $id = $id[1];
                if (is_numeric($id)) {
                    $query->where("t.id = {$this->_db->q($id)}");
                }
            } else {
                $text = $this->_db->q("%{$search}%");
                $query->where("(t.title like {$text} or t.text like {$text})");
            }
        }
        $managerID = JFactory::getUser()->id;
        $query->where("t.managerID = {$this->_db->q($managerID)}");

        /* Сортировка */
        $orderCol = $this->state->get('list.ordering');
        $orderDirn = $this->state->get('list.direction');

        //Ограничение длины списка
        $limit = (!$this->export) ? $this->getState('list.limit') : 0;

        $query->order($this->_db->escape($orderCol . ' ' . $orderDirn));
        $this->setState('list.limit', $limit);

        return $query;
    }

    public function getItems()
    {
        $items = parent::getItems();
        $result = ['items' => []];
        $return = PrjHelper::getReturnUrl();
        foreach ($items as $item) {
            $arr = [];
            $arr['id'] = $item->id;
            $arr['text'] = $item->text;
            $code = mb_strtoupper($item->type);
            $arr['type'] = JText::sprintf("COM_SCHEDULER_TEMPLATE_TYPE_{$code}");
            $url = JRoute::_("index.php?option={$this->option}&amp;task=template.edit&amp;id={$item->id}&amp;return={$return}");
            $arr['edit_link'] = JHtml::link($url, $item->title);
            $result['items'][] = $arr;
        }
        return $result;
    }

    protected function populateState($ordering = 't.id', $direction = 'desc')
    {
        $search = $this->getUserStateFromRequest($this->context . '.filter.search', 'filter_search');
        $this->setState('filter.search', $search);
        parent::populateState($ordering, $direction);
        PrjHelper::check_refresh();
    }

    protected function getStoreId($id = '')
    {
        $id .= ':' . $this->getState('filter.search');
        return parent::getStoreId($id);
    }

    private $export;
}
