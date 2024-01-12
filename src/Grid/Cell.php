<?php

declare(strict_types=1);

namespace SudokuSolver\Grid;

use SudokuSolver\Grid\Cell\Coordinates;
use SudokuSolver\Grid\Cell\Value;

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
