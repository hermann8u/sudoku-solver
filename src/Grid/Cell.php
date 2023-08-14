<?php

declare(strict_types=1);

namespace SudokuSolver\Grid;

use SudokuSolver\Grid\Cell\CellValue;
use SudokuSolver\Grid\Cell\Coordinates;
use SudokuSolver\Grid\Group\RegionNumber;

abstract readonly class Cell
{
    public RegionNumber $regionNumber;

    public function __construct(
        public Coordinates $coordinates,
        public CellValue $cellValue,
    ) {
        $this->regionNumber = RegionNumber::fromCoordinates($this->coordinates);
    }

    public function getCellValue(): CellValue
    {
        return $this->cellValue;
    }

    public function isEmpty(): bool
    {
        return $this->cellValue->isEmpty();
    }

    public function is(Cell $cell): bool
    {
        return $this->coordinates->is($cell->coordinates);
    }
}
