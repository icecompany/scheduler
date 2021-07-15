<?php
defined('_JEXEC') or die;
JHtml::_('bootstrap.tooltip');
JHtml::_('behavior.formvalidation');
JHtml::_('behavior.keepalive');
JHtml::_('formbehavior.chosen', 'select');

use Joomla\CMS\HTML\HTMLHelper;

HTMLHelper::_('script', $this->script);
HTMLHelper::_('script', 'com_scheduler/task.js', array('version' => 'auto', 'relative' => true));
?>
<?php if ($this->version > 0): ?>
    <div class="container-fluid alert alert-danger">
        <h2><?php echo JText::sprintf('COM_MKV_HEAD_VERSION_FROM', JDate::getInstance($this->versionObject->dat)->format("d.m.Y H:i"));?></h2>
    </div>
<?php endif;?>
<form action="<?php echo PrjHelper::getActionUrl(); ?>"
      method="post" name="adminForm" id="adminForm" xmlns="http://www.w3.org/1999/html" class="form-validate">
    <div class="row-fluid">
        <div class="span12 form-horizontal">
            <?php echo JHtml::_('bootstrap.startTabSet', 'myTab', array('active' => 'general', 'useCookie' => true)); ?>
            <div class="tab-content">
                <?php echo JHtml::_('bootstrap.addTab', 'myTab', 'general', JText::sprintf('COM_MKV_TITLE_TAB_GENERAL')); ?>
                <div class="row-fluid">
                    <div class="span4">
                        <div><?php echo $this->loadTemplate('general'); ?></div>
                    </div>
                    <div class="span8">
                        <div><?php echo $this->loadTemplate('meet'); ?></div>
                        <div><?php echo $this->loadTemplate('count'); ?></div>
                        <div><?php echo $this->loadTemplate('contacts'); ?></div>
                    </div>
                </div>
                <?php echo JHtml::_('bootstrap.endTab'); ?>
                <?php if ($this->item->id !== null): ?>
                    <?php echo JHtml::_('bootstrap.addTab', 'myTab', 'tasks', JText::sprintf('COM_SCHEDULER_TAB_OTHER_TASKS')); ?>
                    <div><?php echo $this->loadTemplate('tasks'); ?></div>
                    <?php echo JHtml::_('bootstrap.endTab'); ?>
                <?php endif; ?>
                <?php echo JHtml::_('bootstrap.addTab', 'myTab', 'history', JText::sprintf('COM_MKV_LAYOUT_HISTORY')); ?>
                <div class="row-fluid">
                    <div class="span12">
                        <div><?php echo $this->loadTemplate('history'); ?></div>
                    </div>
                </div>
                <?php echo JHtml::_('bootstrap.endTab'); ?>
            </div>
            <?php echo JHtml::_('bootstrap.endTabSet'); ?>
        </div>
        <div>
            <input type="hidden" name="task" value=""/>
            <?php echo JHtml::_('form.token'); ?>
        </div>
    </div>
</form>

