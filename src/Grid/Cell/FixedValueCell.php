<?php

declare(strict_types=1);

namespace SudokuSolver\Grid\Cell;


use SudokuSolver\Grid\Cell;

final readonly class FixedValueCell extends Cell
{
    public function __construct(Coordinates $coordinates, CellValue $cellValue)
    {
        if ($cellValue->isEmpty()) {
            throw new \DomainException();
        }

        parent::__construct($coordinates, $cellValue);
    }

    public function isEmpty(): bool
    {
        return false;
    }
}
