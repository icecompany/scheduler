<?php
defined('_JEXEC') or die;
?>
<table class="table table-stripped">
    <thead>
        <tr>
            <th><?php echo JText::sprintf('COM_COMPANIES_HEAD_CONTACTS_FIO');?></th>
            <th><?php echo JText::sprintf('COM_COMPANIES_HEAD_CONTACTS_POST');?></th>
            <th><?php echo JText::sprintf('COM_COMPANIES_HEAD_CONTACTS_WORK_PHONE');?></th>
            <th><?php echo JText::sprintf('COM_COMPANIES_HEAD_CONTACTS_MOBILE_PHONE');?></th>
            <th><?php echo JText::sprintf('COM_COMPANIES_HEAD_CONTACTS_EMAIL');?></th>
            <th><?php echo JText::sprintf('COM_COMPANIES_HEAD_CONTACTS_FOR_ACCREDITATION');?></th>
            <th><?php echo JText::sprintf('COM_COMPANIES_HEAD_CONTACTS_FOR_BUILDING');?></th>
            <th></th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($this->item->contacts as $contact): ?>
            <tr>
                <td><?php echo $contact['fio'];?></td>
                <td><?php echo $contact['post'];?></td>
                <td><?php echo $contact['phone_work'];?></td>
                <td><?php echo $contact['phone_mobile'];?></td>
                <td><?php echo $contact['email'];?></td>
                <td><?php echo $contact['for_accreditation'];?></td>
                <td><?php echo $contact['for_building'];?></td>
                <td><?php echo $contact['delete_link'];?></td>
            </tr>
        <?php endforeach;?>
    </tbody>
</table>
