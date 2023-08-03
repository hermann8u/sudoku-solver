<?php

declare(strict_types=1);

namespace Florian\SudokuSolver\Grid\Cell;


use Florian\SudokuSolver\Grid\Cell;

final class FillableCell extends Cell
{
    public function updateValue(CellValue $value): void
    {
        $this->cellValue = $value;
    }
}
