<?php

namespace Bogdancondorachi\TableField;

use Kirby\Data\Data;
use Throwable;

/**
 * Parses stored table values into a list of rows
 */
class Table
{
  /**
   * Converts the stored YAML string or an array into
   * a list of rows with trimmed string cells
   */
  public static function rows(mixed $value): array
  {
    if (is_string($value) === true) {
      try {
        $value = Data::decode($value, 'yaml');
      } catch (Throwable) {
        return [];
      }
    }

    if (is_array($value) === false) {
      return [];
    }

    $rows = [];

    foreach ($value as $row) {
      if (is_array($row) === false) {
        continue;
      }

      $rows[] = array_map(
        // trim the trailing newline of YAML block scalars,
        // as written for multiline cells by the Spyc handler
        fn ($cell) => is_scalar($cell) === true ? trim((string)$cell) : '',
        array_values($row)
      );
    }

    return $rows;
  }
}
