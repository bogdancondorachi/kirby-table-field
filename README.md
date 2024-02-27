![Kirby Table Field](./.github/preview.png)

# Kirby Table Field
The table field makes possible to effortlessly create and manage tables within the panel. You can easily tailor tables to their specific needs, enhancing the efficiency and management of data presentation.

> [!WARNING]
> **The plugin requires Kirby 4**. For Kirby 3, you can use [v2.1.0](https://github.com/bogdancondorachi/kirby-table-field/releases/tag/v2.1.0) (although without further support)

## Installation

### Composer

```bash
composer require bogdancondorachi/kirby-table-field
```

### Git Submodule
```bash
git submodule add https://github.com/bogdancondorachi/kirby-table-field.git site/plugins/table-field
```

### Manual

[Download](https://api.github.com/repos/bogdancondorachi/kirby-table-field/zipball) and extract the folder to `/site/plugins/table-field`

## Field Usage

### Add the field to your blueprint:
```yaml
fields:
  table:
    label: Table
    type: table
    #optional (see field properties)
```

### Field Properties:
| Name       | Type          | Default | Description                                                      |
|:-----------|:--------------|:--------|:-----------------------------------------------------------------|
| align      | `string`      | `-`     | Set the text alignment of the table
| disabled   | `bool`        | `-`     | If `true`, the field is no longer editable and will not be saved |
| duplicate  | `bool`        | `true`  | Toggles duplicating columns and rows in the table                |
| empty      | `string`      | `-`     | The placeholder text if no rows exists                           |
| help       | `string`      | `-`     | Optional help text below the field                               |
| index      | `int`, `bool` | `1`     | Specifies the starting index. If set to `false`, it removes the index column; in this case, `sortable` would be disabled as well                                                                                 |
| label      | `string`      | `-`     | Set the label above the field                                    |
| maxColumns | `int`         | `8`     | Set the maximum allowed columns in the table                     |
| minColumns | `int`         | `2`     | Set the minimum required columns in the table                    |
| sortable   | `bool`        | `true`  | Toggles drag & drop sorting                                      |
| translate  | `bool`        | `true`  | If `false`, the field will be disabled in non-default languages and cannot be translated. This is only relevant in multi-language setups.                                                                        |

### Use the field in your template:
```php
<?php 
$table = $page->table()->toTable();
if (!empty($table['headers']) && !empty($table['rows'])): ?>
  <table>
    <thead>
      <tr>
        <?php foreach ($table['headers'] as $header): ?>
          <th><?= $header ?></th>
        <?php endforeach ?>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($table['rows'] as $row): ?>
        <tr>
          <?php foreach ($row as $cell): ?>
            <td><?= $cell ?></td>
          <?php endforeach ?>
        </tr>
      <?php endforeach ?>
    </tbody>
  </table>
<?php endif ?>
```

## Blocks Usage

### Add the block to your blueprint:
```yaml
fields:
  text:
    type: blocks
    fieldsets:
      - table
```
### Blueprint:
```yaml
name: Table
icon: table
preview: table
fields:
  table:
    type: table
```
To overwrite this default blueprint, place your custom file in `/site/blueprints/blocks/table.yml`

### Snippet:
```php
<?php 
$table = $block->table()->toTable();
if (!empty($table['headers']) && !empty($table['rows'])): ?>
  <table>
    <thead>
      <tr>
        <?php foreach ($table['headers'] as $header): ?>
          <th><?= $header ?></th>
        <?php endforeach ?>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($table['rows'] as $row): ?>
        <tr>
          <?php foreach ($row as $cell): ?>
            <td><?= $cell ?></td>
          <?php endforeach ?>
        </tr>
      <?php endforeach ?>
    </tbody>
  </table>
<?php endif ?>
```
To overwrite this default snippet, place your custom file in `/site/snippets/blocks/table.php`

## Credits

- [Kirby Team](https://getkirby.com) for the [table](https://github.com/getkirby/kirby/blob/main/panel/src/components/Layout/Table.vue) layout.
- [Rafael Giezendanner](https://github.com/ragi96) for the initial [table-field](https://github.com/ragi96/table-field) plugin.

## License

[MIT License](./LICENSE)
Copyright © 2024
[Bogdan Condorachi](https://github.com/bogdancondorachi)
