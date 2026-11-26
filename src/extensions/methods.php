<?php

use Bogdancondorachi\TableField\Table;
use Kirby\Content\Field;

return [
  'toTable' => function (Field $field) {
    $rows = Table::rows($field->value());

    // Check blueprint for header configuration
    $blueprint  = $field->parent()?->blueprint()->field($field->key());
    $hasHeaders = $blueprint['headers'] ?? true;
    $headers    = $hasHeaders === true && $rows !== [] ? array_shift($rows) : [];

    return [
      'headers' => $headers,
      'rows'    => $rows
    ];
  }
];
