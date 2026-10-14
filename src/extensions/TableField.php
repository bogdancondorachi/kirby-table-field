<?php

namespace Bogdancondorachi\TableField;

use Kirby\Form\Field\InputField;
use Kirby\Form\Mixin;
use Kirby\Reflection\Attributes\Prop;

/**
 * Allows editors to manage a simple table with
 * text headers and writer cells
 */
class TableField extends InputField
{
  use Mixin\Duplicate;
  use Mixin\EmptyState;
  use Mixin\Sortable;

  /**
   * Text alignment of all cells
   */
  #[Prop]
  protected string|null $align = null;

  /**
   * If `false`, the first row is treated as a regular row
   */
  #[Prop]
  protected bool $headers = true;

  /**
   * Start number of the index column or `false` to hide it
   */
  #[Prop]
  protected bool|int $index = 1;

  /**
   * Allowed writer marks in cells
   */
  #[Prop]
  protected array|bool $marks = true;

  /**
   * Upper limit of columns
   */
  #[Prop]
  protected int $maxColumns = 8;

  /**
   * Lower limit of columns
   */
  #[Prop]
  protected int $minColumns = 2;

  protected array $value = [];

  public function align(): string|null
  {
    return $this->align;
  }

  public function fill(mixed $value): static
  {
    return parent::fill(
      value: Table::rows($value)
    );
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
}
