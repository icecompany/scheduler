<?php

defined('_JEXEC') or die;

use Joomla\CMS\Table\Table;

class TableSchedulerMeetings extends Table
{
    var $id = null;
    var $taskID = null;
    var $contactID = null;
    var $place = null;
    var $theme = null;

	public function __construct(JDatabaseDriver $db)
	{
		parent::__construct('#__mkv_meetings', 'id', $db);
	}

	public function store($updateNulls = true)
    {
        return parent::store($updateNulls);
    }
}
