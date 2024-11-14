<?php

use Kirby\Content\Field;
use Kirby\Toolkit\Str;

return [
  'toTable' => function (Field $field) {
    // Helpers for value processing
    $clearValue = fn($value) => trim(preg_replace('/^[>\|-]?\s*["\']?(.*?)["\']?$/', '$1', $value) ?? '');
    $isRowBreak = fn($value) => preg_match('/^\s*-\s*$/', $value);
    $isMultiline = fn($value) => preg_match('/^\s*-\s*[>|]\s*$/', $value);

    // Parses rows from the provided string content.
    $parseRows = function ($input) use ($clearValue, $isRowBreak, $isMultiline) {
      $rows = [];
      $currentRow = [];
      $currentCell = '';
      $inMultilineCell = false;

      foreach (Str::split($input, "\n") as $line) {
        $line = trim($line);

        if ($isRowBreak($line)) {
          if ($inMultilineCell) {
            $currentRow[] = $clearValue($currentCell);
            $currentCell = '';
            $inMultilineCell = false;
          }
          if (!empty($currentRow)) {
            $rows[] = $currentRow;
            $currentRow = [];
          }
        } elseif ($isMultiline($line)) {
          if ($inMultilineCell) {
            $currentRow[] = $clearValue($currentCell);
            $currentCell = '';
          }
          $inMultilineCell = true;
        } elseif ($inMultilineCell) {
          if (strpos($line, '-') === 0 && !preg_match('/^\s*-\s*>/', $line)) {
            $inMultilineCell = false;
            $currentRow[] = $clearValue($currentCell);
            $currentCell = '';
            if (!$isRowBreak($line)) {
              $currentRow[] = $clearValue($line);
            }
          } else {
            $currentCell .= ($currentCell ? "\n" : '') . $line;
          }
        } else {
          $currentRow[] = $clearValue($line);
        }
      }

      if ($inMultilineCell && trim($currentCell)) {
        $currentRow[] = $clearValue($currentCell);
      }
      if (!empty($currentRow)) {
        $rows[] = $currentRow;
      }

      return $rows;
    };

    // Get field value and parse rows if it's a string
    $value = $field->value();
    $rows = is_string($value) ? $parseRows($value) : (is_array($value) ? $value : []);

    // Check blueprint for header configuration
    $blueprint = $field->parent()->blueprint()->field($field->key());
    $hasHeaders = $blueprint['headers'] ?? true;
    $headers = $hasHeaders && !empty($rows) ? array_shift($rows) : [];

    return [
      'headers' => $headers,
      'rows' => $rows
    ];
  }
];
