<?php

namespace Bogdancondorachi\TableField;

use Kirby\Form\FieldClass;
use Kirby\Form\Mixin\EmptyState;

/**
 * Allows editors to manage a simple table with
 * text headers and writer cells
 */
class TableField extends FieldClass
{
  use EmptyState;

  /**
   * Text alignment of all cells
   */
  protected string|null $align = null;

  /**
   * If `false`, the first row is treated as a regular row
   */
  protected bool $headers = true;

  /**
   * Start number of the index column or `false` to hide it
   */
  protected bool|int $index = 1;

  /**
   * Allowed writer marks in cells
   */
  protected array|bool $marks = true;

  /**
   * Upper limit of columns
   */
  protected int $maxColumns = 8;

  /**
   * Lower limit of columns
   */
  protected int $minColumns = 2;

  protected bool $duplicate = true;
  protected bool $sortable = true;
  protected mixed $value = [];

  public function __construct(array $params = [])
  {
    parent::__construct($params);

    $this->align = $params['align'] ?? null;
    $this->duplicate = $params['duplicate'] ?? true;
    $this->headers = $params['headers'] ?? true;
    $this->index = $params['index'] ?? 1;
    $this->marks = $params['marks'] ?? true;
    $this->maxColumns = $params['maxColumns'] ?? 8;
    $this->minColumns = $params['minColumns'] ?? 2;
    $this->setEmpty($params['empty'] ?? null);
    $this->sortable = $params['sortable'] ?? true;
  }

  public function align(): string|null
  {
    return $this->align;
  }

  public function headers(): bool
  {
    return $this->headers;
  }

  public function index(): bool|int
  {
    return $this->index;
  }

  public function marks(): array|bool
  {
    return $this->marks;
  }

  public function maxColumns(): int
  {
    return $this->maxColumns;
  }

  public function minColumns(): int
  {
    return $this->minColumns;
  }

  public function props(): array
  {
    return [
      'align'       => $this->align(),
      'duplicate'   => $this->duplicate,
      'empty'       => $this->empty(),
      'headers'     => $this->headers(),
      'index'       => $this->index(),
      'marks'       => $this->marks(),
      'maxColumns'  => $this->maxColumns(),
      'minColumns'  => $this->minColumns(),
      'sortable'    => $this->sortable,
    ] + parent::props();
  }

  public function toFormValue(): mixed
  {
    return Table::rows($this->value);
  }

  public function value(bool $default = false): mixed
  {
    return Table::rows(parent::value($default));
  }
}
