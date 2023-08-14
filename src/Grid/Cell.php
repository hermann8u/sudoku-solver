<?php

declare(strict_types=1);

namespace SudokuSolver\Grid;

use SudokuSolver\Grid\Cell\Coordinates;
use SudokuSolver\Grid\Cell\Value;
use SudokuSolver\Grid\Group\Number\RegionNumber;

abstract readonly class Cell
{
    public RegionNumber $regionNumber;

    public function __construct(
        public Coordinates $coordinates,
        public Value $value,
    ) {
        $this->regionNumber = RegionNumber::fromCell($this);
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
