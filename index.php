<?php

use Bogdancondorachi\TableField\TableField;

load([
  'Bogdancondorachi\\TableField\\Table'      => 'src/classes/Table.php',
  'Bogdancondorachi\\TableField\\TableField' => 'src/classes/TableField.php'
], __DIR__);

Kirby::plugin('bogdancondorachi/table-field', [
  'fields' => [
    'table' => TableField::class
  ],

  'blueprints' => [
    'blocks/table' => __DIR__ . '/src/extensions/blueprints/blocks/table.yml'
  ],

  'snippets' => [
    'blocks/table' => __DIR__ . '/src/extensions/snippets/blocks/table.php'
  ],

  'fieldMethods' => require_once __DIR__ . '/src/extensions/methods.php',

  'translations' => require_once __DIR__ . '/src/extensions/translations.php'
]);
