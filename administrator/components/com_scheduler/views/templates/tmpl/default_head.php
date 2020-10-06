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
        <?php echo JHtml::_('searchtools.sort', 'COM_SCHEDULER_HEAD_TEMPLATE_TYPE', 't.type', $listDirn, $listOrder); ?>
    </th>
    <th>
        <?php echo JText::sprintf('COM_MKV_HEAD_TITLE'); ?>
    </th>
    <th>
        <?php echo JText::sprintf('COM_SCHEDULER_HEAD_TEMPLATE_TEXT'); ?>
    </th>
    <th>
        <?php echo JHtml::_('searchtools.sort', 'ID', 't.id', $listDirn, $listOrder); ?>
    </th>
</tr>
