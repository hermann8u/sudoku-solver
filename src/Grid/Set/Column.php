<?php

declare(strict_types=1);

namespace Florian\SudokuSolver\Grid\Set;

use Florian\SudokuSolver\Grid\Cell;
use Florian\SudokuSolver\Grid\Set;

final readonly class Column extends Set
{
    /**
     * @param Cell[] $cells
     */
    public static function fromCells(array $cells, int $x): self
    {
        return new self(
            array_values(array_filter($cells, static fn (Cell $cell) => $cell->coordinates->x === $x)),
        );
    }
}
