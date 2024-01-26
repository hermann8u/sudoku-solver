<?php

declare(strict_types=1);

namespace Sudoku\Grid;

use Sudoku\Grid\Cell\Coordinates;
use Sudoku\Grid\Cell\Value;

abstract readonly class Cell
{
    public function __construct(
        public Coordinates $coordinates,
        public ?Value $value = null,
    ) {
    }

    public function isEmpty(): bool
    {
        return $this->value === null;
    }

    public function is(Cell $cell): bool
    {
        return $this->coordinates->equals($cell->coordinates);
    }
}
