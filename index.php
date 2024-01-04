<?php

Kirby::plugin('bogdancondorachi/table-field', [
  'fields' => [
    'table' => []
  ],
  
  'fieldMethods' => [
    'toTable' => function ($field) {
      if ($field->value() === null) {
        return ['headers' => [], 'rows' => []];
      }

      if (is_string($field->value())) {
        $array = str::split($field, "\n");
        $newRow = $rows = [];
  
        foreach ($array as $element) {
          $element = trim($element);

          if ($element === '-') {
            if (!empty($newRow)) {
              $rows[] = $newRow;
              $newRow = [];
            }
          } else {
            $element = trim($element, '- ');
            $element = trim($element, '"\'');
            $newRow[] = $element;
          }
        }

        $rows[] = $newRow;
        $headers = array_shift($rows) ?? [];
  
        return ['headers' => $headers, 'rows' => $rows];
      } else {
        return $field->toArray()[$field->key()];
      }
    }
  ]
]);
