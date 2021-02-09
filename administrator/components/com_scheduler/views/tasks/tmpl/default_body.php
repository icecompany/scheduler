<?php
// Запрет прямого доступа.
defined('_JEXEC') or die;
$ii = $this->state->get('list.start', 0);
$arr = [-2, 1, 2, 3];
foreach ($arr as $status):?>
    <?php foreach ($this->items['items'][$status] as $i => $item) : ?>
        <tr class="row<?php echo $i % 2; ?>">
            <td class="center">
                <?php echo JHtml::_('grid.id', $i, $item['id']); ?>
            </td>
            <td>
                <?php echo ++$ii; ?>
            </td>
            <td>
                <?php echo $item['status']; ?>
            </td>
            <td>
                <?php echo $item['date_task']; ?>
            </td>
            <td>
                <?php echo $item['contract_link']; ?>
            </td>
            <td>
                <?php echo $item['edit_link']; ?>
            </td>
            <?php if (!is_numeric(PrjHelper::getActiveProject())): ?>
                <td>
                    <?php echo $item['project']; ?>
                </td>
            <?php endif;?>
            <td>
                <?php echo $item['company_link']; ?>
            </td>
            <td>
                <?php echo $item['tasks_link']; ?>
            </td>
            <td>
                <?php echo $item['result']; ?>
            </td>
            <td>
                <?php echo $item['manager']; ?>
            </td>
            <td>
                <?php echo $item['date_close']; ?>
            </td>
        </tr>
    <?php endforeach; ?>
<?php endforeach; ?>
