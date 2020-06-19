<?php

defined('_JEXEC') or die;

use Joomla\CMS\Table\Table;

class TableSchedulerChannels extends Table
{
    var $id = null;
    var $channelID = null;
    var $api_key = null;
    var $managerID = null;
    var $uid = null;

    public function __construct(JDatabaseDriver $db)
	{
		parent::__construct('#__mkv_managers_push_channels', 'id', $db);
	}

	public function store($updateNulls = true)
    {
        return parent::store($updateNulls);
    }
}
