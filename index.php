<?php

Kirby::plugin('bogdancondorachi/table-field', [
  'fields' => [
    'table' => []
  ],

  'blueprints' => [
    'blocks/table' => __DIR__ . '/blueprints/blocks/table.yml'
  ],

  'snippets' => [
    'blocks/table' => __DIR__ . '/snippets/blocks/table.php'
  ],

  'fieldMethods' => require_once __DIR__ . '/lib/methods.php',

  'translations' => require_once __DIR__ . '/lib/translations.php'
]);
