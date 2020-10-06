<?php
// Запрет прямого доступа.
defined('_JEXEC') or die;
$colspan = '6';?>
<tr>
    <td colspan="<?php echo $colspan;?>"><?php echo $this->pagination->getListFooter(); ?></td>
</tr>