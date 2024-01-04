![Kirby Table Field](./.github/preview.png)

# Kirby Table Field
The table field streamlines the management of structured data in tables, offering a user-friendly interface that simplifies data entry and enhances content creation by making tabular information effortlessly manageable.

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

[Download](https://api.github.com/repos/bogdancondorachi/kirby-table-field/zipball) and extract the folder to `/site/plugins/kirby-table-field`

## Usage

#### Add the field to your blueprint:
```yaml
fields:
  table:
    label: table
    type: table
    # optional, default values
    minColumns: 2
    maxColumns: 5
```

#### Use the field in your template:
```php
<?php $table = $page->table()->toTable(); ?>
<?php if($table != null): ?>
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

## Credits

- [Rafael Giezendanner](https://github.com/ragi96) for the initial [table-field](https://github.com/ragi96/table-field) plugin.

## License

[MIT License](./LICENSE)
Copyright © 2024
[Bogdan Condorachi](https://github.com/bogdancondorachi)
