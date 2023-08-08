<?php

declare(strict_types=1);

namespace SudokuSolver\Tests\Double\Grid;

use SudokuSolver\Grid\Cell;

final class NullCell extends Cell
{
    /**
     * @return NullCell[]
     */
    public static function multiple(): array
    {
        $cells = [];

        for ($x = 1; $x < 10; $x++) {
            $cells[] = new self(new Cell\Coordinates($x, 1));
        }

        return $cells;
    }
}
