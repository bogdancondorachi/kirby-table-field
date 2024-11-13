<?php
/** @var \Kirby\Cms\Block $block */
$table   = $block->table()->toTable();
$caption = $block->caption();  
?>
<?php if($table != null): ?>
<table>
  <caption><?= $block->caption() ?></caption>
  <thead>
    <tr>
      <?php foreach ($table['headers'] as $header): ?>
        <th><?= $header ?></th>
      <?php endforeach ?>
    </tr>
  </thead>
  <tbody>
    <?php foreach ($table['rows'] as $row): ?>
      <tr>
        <?php foreach ($row as $cell): ?>
          <td><?= $cell ?></td>
        <?php endforeach ?>
      </tr>
    <?php endforeach ?>
  </tbody>
</table>
<?php endif ?>
