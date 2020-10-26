<?php
defined('_JEXEC') or die;
?>
<table class="table table-stripped">
    <thead>
        <tr>
            <th><?php echo JText::sprintf('COM_MKV_HEAD_DATE');?></th>
            <th><?php echo JText::sprintf('COM_MKV_HEAD_MANAGER');?></th>
            <th><?php echo JText::sprintf('COM_MKV_HEAD_VERSION_ACTION');?></th>
            <th><?php echo JText::sprintf('COM_MKV_HEAD_VERSION_SHOW');?></th>
            <th>ID</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($this->history as $history): ?>
            <tr>
                <td><?php echo $history['dat'];?></td>
                <td><?php echo $history['manager'];?></td>
                <td><?php echo $history['action'];?></td>
                <td><?php echo $history['show_link'];?></td>
                <td><?php echo $history['id'];?></td>
            </tr>
        <?php endforeach;?>
    </tbody>
</table>
