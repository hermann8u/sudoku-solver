<?php

declare(strict_types=1);

namespace SudokuSolver\Grid;

use SudokuSolver\Grid\Cell\Coordinates;
use SudokuSolver\Grid\Cell\Value;

abstract readonly class Cell
{
    protected function __construct(
        public Coordinates $coordinates,
        public Value $value,
    ) {
    }

    public function isEmpty(): bool
    {
        return $this->value->isEmpty();
    }

    public function is(Cell $cell): bool
    {
        return $this->coordinates->is($cell->coordinates);
    }
}
