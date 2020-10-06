<?php

defined('_JEXEC') or die;

use Joomla\CMS\Table\Table;

class TableSchedulerTemplates extends Table
{
    var $id = null;
    var $managerID = null;
    var $type = null;
    var $title = null;
    var $text = null;

	public function __construct(JDatabaseDriver $db)
	{
		parent::__construct('#__mkv_task_templates', 'id', $db);
	}

	public function store($updateNulls = true)
    {
        return parent::store($updateNulls);
    }
}
