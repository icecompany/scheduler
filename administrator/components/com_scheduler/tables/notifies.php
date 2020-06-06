<?php

defined('_JEXEC') or die;

use Joomla\CMS\Table\Table;

class TableSchedulerNotifies extends Table
{
    var $id = null;
    var $date_create = null;
    var $date_read = null;
    var $user_create = null;
    var $managerID = null;
    var $contractID = null;
    var $text = null;
    var $status = null;

	public function __construct(JDatabaseDriver $db)
	{
		parent::__construct('#__mkv_notifies', 'id', $db);
	}

	public function store($updateNulls = true)
    {
        return parent::store($updateNulls);
    }
}
