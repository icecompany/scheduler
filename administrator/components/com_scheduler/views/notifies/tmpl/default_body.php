<?php
// Запрет прямого доступа.
defined('_JEXEC') or die;
$ii = $this->state->get('list.start', 0);
$arr = [-2, 1, 2, 3];
?>
<?php foreach ($this->items['items'] as $i => $item) : ?>
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
            <?php echo $item['date_create']; ?>
        </td>
        <td>
            <?php echo $item['contract_link']; ?>
        </td>
        <td>
            <?php echo $item['company_link']; ?>
        </td>
        <td>
            <?php echo $item['text']; ?>
        </td>
        <td>
            <?php echo $item['manager']; ?>
        </td>
        <td>
            <?php echo $item['id']; ?>
        </td>
    </tr>
<?php endforeach; ?>
