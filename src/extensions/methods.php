<?php

use Bogdancondorachi\TableField\Table;
use Kirby\Cms\Block;
use Kirby\Cms\Blueprint;
use Kirby\Cms\Fieldsets;
use Kirby\Content\Field;

return [
  'toTable' => function (Field $field, Block|null $block = null) {
    $rows = Table::rows($field->value());

    if ($block === null) {
      $fieldBlueprint = $field->parent()?->blueprint()->field($field->key());
    } else {
      $fieldBlueprint = null;
      $blocksField = $block->field();

      if ($blocksField !== null) {
        $blocksFieldBlueprint = $blocksField->parent()?->blueprint()->field($blocksField->key());

        if (($blocksFieldBlueprint['type'] ?? null) === 'blocks') {
          $fieldsets = $blocksFieldBlueprint['fieldsets'] ?? null;

          if (is_string($fieldsets) === true) {
            $fieldsets = [];
          }

          $fieldset = Fieldsets::factory(
            $fieldsets,
            ['parent' => $block->parent()]
          )->find($block->type());
          $fieldsetFields = $fieldset?->fields() ?? [];
          $fieldBlueprint = $fieldsetFields[$field->key()] ?? null;
        }
      }

      if ($fieldBlueprint === null) {
        $blockBlueprint = Blueprint::factory(
          'blocks/' . $block->type(),
          null,
          $block->parent()
        );
        $fieldBlueprint = $blockBlueprint?->field($field->key());
      }
    }

    $hasHeaders = $fieldBlueprint['headers'] ?? true;
    $headers    = $hasHeaders === true && $rows !== [] ? array_shift($rows) : [];

    return [
      'headers' => $headers,
      'rows'    => $rows
    ];
  }
];
