<?php

use Kirby\Content\Field;
use Kirby\Toolkit\Str;

return [
  'toTable' => function (Field $field) {
    if (!is_string($field->value())) {
      return ['headers' => [], 'rows' => []];
    } else {
      $items = Str::split($field, "\n");
      $newRow = [];
      $rows = [];

      foreach ($items as $item) {
        $item = trim($item);

        if ($item === '-') {
          if (!empty($newRow)) {
            $rows[] = $newRow;
            $newRow = [];
          }
        } else {
          $item = trim($item, '- ');
          $item = trim($item, '"\'');
          $newRow[] = $item;
        }
      }

      if (!empty($newRow)) {
        $rows[] = $newRow;
      }

      $blueprint = $field->parent()->blueprint()->field($field->key());
      $hasHeaders = $blueprint['headers'] ?? true;
      $headers = $hasHeaders ? array_shift($rows) : [];

      return ['headers' => $headers, 'rows' => $rows];
    }
  }
];
