<?php

defined('_JEXEC') or die;

use Joomla\CMS\Table\Table;

class TableSchedulerScheduler extends Table
{
    var $id = null;
    var $date_create = null;
    var $date_task = null;
    var $date_close = null;
    var $contractID = null;
    var $managerID = null;
    var $user_open = null;
    var $user_close = null;
    var $status = null;
    var $task = null;
    var $result = null;

	public function __construct(JDatabaseDriver $db)
	{
		parent::__construct('#__mkv_scheduler', 'id', $db);
	}

	public function store($updateNulls = true)
    {
        return parent::store($updateNulls);
    }
}
