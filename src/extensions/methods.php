<?php

use Kirby\Content\Field;
use Kirby\Toolkit\Str;

return [
  'toTable' => function (Field $field) {
    $value = $field->value();

    if (is_string($value)) {
      $items = Str::split($field, "\n");
      $currentRow = [];
      $rows = [];

      foreach ($items as $item) {
        $item = trim($item);

        if ($item === '-') {
          if (!empty($currentRow)) {
            $rows[] = $currentRow;
            $currentRow = [];
          }
        } else {
          $item = trim($item, '- ');
          $item = trim($item, '"\'');
          $currentRow[] = $item;
        }
      }

      if (!empty($currentRow)) {
        $rows[] = $currentRow;
      }

      $blueprint = $field->parent()->blueprint()->field($field->key());
      $hasHeaders = $blueprint['headers'] ?? true;
      $headers = $hasHeaders ? array_shift($rows) : [];

    } else {

      $value = is_array($value) ? $value : [];
      $headers = !empty($value) ? array_shift($value) : [];
      $rows = !empty($value) ? $value : [];

    }
    
    return ['headers' => $headers, 'rows' => $rows];
  }
];
