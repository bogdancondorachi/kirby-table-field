<?php

Kirby::plugin('bogdancondorachi/table-field', [
  'fields' => [
    'table' => []
  ],

  'fieldMethods' => require_once __DIR__ . '/lib/methods.php',

  'translations' => require_once __DIR__ . '/lib/translations.php'
]);
