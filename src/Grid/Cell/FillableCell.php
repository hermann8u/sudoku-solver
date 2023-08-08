<?php

declare(strict_types=1);

namespace SudokuSolver\Grid\Cell;


use SudokuSolver\Grid\Cell;

final class FillableCell extends Cell
{
    public function updateValue(CellValue $value): void
    {
        $this->cellValue = $value;
    }
}
