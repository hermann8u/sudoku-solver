<?php

declare(strict_types=1);

namespace Florian\SudokuSolver\Grid\Cell;


use Florian\SudokuSolver\Grid\Cell;

final class FixedValueCell extends Cell
{
    public function __construct(Coordinates $coordinates, CellValue $cellValue)
    {
        parent::__construct($coordinates);

        if ($cellValue->isEmpty()) {
            throw new \DomainException();
        }

        $this->cellValue = $cellValue;
    }

    public function isEmpty(): bool
    {
        return false;
    }
}
