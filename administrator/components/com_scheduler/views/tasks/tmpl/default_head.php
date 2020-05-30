<?php
defined('_JEXEC') or die;
$listOrder    = $this->escape($this->state->get('list.ordering'));
$listDirn    = $this->escape($this->state->get('list.direction'));
?>
<tr>
    <th style="width: 1%;">
        <?php echo JHtml::_('grid.checkall'); ?>
    </th>
    <th style="width: 1%;">
        №
    </th>
    <th>
        <?php echo JHtml::_('searchtools.sort', 'COM_MKV_HEAD_STATUS', 's.status, s.date_task', $listDirn, $listOrder); ?>
    </th>
    <th>
        <?php echo JHtml::_('searchtools.sort', 'COM_MKV_HEAD_DATE', 's.date_task', $listDirn, $listOrder); ?>
    </th>
    <th>
        <?php echo JText::sprintf('COM_MKV_HEAD_OPEN'); ?>
    </th>
    <th>
        <?php echo JHtml::_('searchtools.sort', 'COM_MKV_HEAD_COMPANY', 'company', $listDirn, $listOrder); ?>
    </th>
    <th>
        <?php echo JText::sprintf('COM_MKV_HEAD_TASK'); ?>
    </th>
    <th>
        <?php echo JText::sprintf('COM_MKV_HEAD_RESULT'); ?>
    </th>
    <th>
        <?php echo JHtml::_('searchtools.sort', 'COM_MKV_HEAD_EXECUTOR', 'u.name', $listDirn, $listOrder); ?>
    </th>
    <th style="width: 1%;">
        <?php echo JHtml::_('searchtools.sort', 'ID', 's.id', $listDirn, $listOrder); ?>
    </th>
</tr>
