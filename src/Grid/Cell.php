<?php

declare(strict_types=1);

namespace Florian\SudokuSolver\Grid;

use Florian\SudokuSolver\Grid\Cell\CellValue;
use Florian\SudokuSolver\Grid\Cell\Coordinates;
use Florian\SudokuSolver\Grid\Group\RegionNumber;

abstract class Cell
{
    protected CellValue $cellValue;
    public readonly RegionNumber $regionNumber;

    public function __construct(
        public readonly Coordinates $coordinates,
    ) {
        $this->cellValue = CellValue::empty();
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
