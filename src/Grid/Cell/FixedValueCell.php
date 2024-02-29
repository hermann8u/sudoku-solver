<?php

declare(strict_types=1);

namespace Sudoku\Grid\Cell;

use Sudoku\Grid\Cell;

final readonly class FixedValueCell extends Cell
{
    public function __construct(Coordinates $coordinates, Value $value)
    {
        parent::__construct($coordinates, $value);
    }

    public function isEmpty(): bool
    {
        return false;
    }
}
