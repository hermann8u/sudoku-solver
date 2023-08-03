<?php

declare(strict_types=1);

namespace Florian\SudokuSolver\Grid;

use Florian\SudokuSolver\Grid\Cell\CellValue;
use Florian\SudokuSolver\Grid\Cell\Coordinates;
use Florian\SudokuSolver\Grid\Set\GroupNumber;

abstract class Cell
{
    protected CellValue $cellValue;
    public readonly GroupNumber $groupNumber;

    public function __construct(
        public readonly Coordinates $coordinates,
    ) {
        $this->cellValue = CellValue::empty();
        $this->groupNumber = GroupNumber::fromCoordinates($this->coordinates);
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
        return $this->coordinates->toString() === $cell->coordinates->toString();
    }

    public function isOnSameRow(Cell $cell): bool
    {
        if ($this->is($cell)) {
            return false;
        }

        return $this->coordinates->y === $cell->coordinates->y;
    }

    public function isOnSameColumn(Cell $cell): bool
    {
        if ($this->is($cell)) {
            return false;
        }

        return $this->coordinates->x === $cell->coordinates->x;
    }

    public function isInSameGroup(Cell $cell): bool
    {
        if ($this->is($cell)) {
            return false;
        }

        return $this->groupNumber->value === $cell->groupNumber->value;
    }
}
