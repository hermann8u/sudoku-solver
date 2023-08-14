<?php

declare(strict_types=1);

namespace SudokuSolver\Grid\Cell;

use SudokuSolver\Grid\Cell;

final readonly class FillableCell extends Cell
{
    public function __construct(Coordinates $coordinates, ?Value $cellValue = null)
    {
        if (! $cellValue instanceof Value) {
            $cellValue = Value::empty();
        }

        parent::__construct($coordinates, $cellValue);
    }
}
