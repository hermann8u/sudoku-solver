<?php

declare(strict_types=1);

namespace SudokuSolver\Grid\Group;

use SudokuSolver\Grid\Cell;
use SudokuSolver\Grid\Group;
use SudokuSolver\Grid\Group\Number\ColumnNumber;

final readonly class Column extends Group
{
    /**
     * @param Cell[] $cells
     */
    private function __construct(
        array $cells,
        ColumnNumber $number,
    ) {
        parent::__construct($cells, $number);
    }

    /**
     * @param Cell[] $cells
     */
    public static function fromAllCells(array $cells, ColumnNumber $number): self
    {
        return new self(
            array_values(array_filter($cells, static fn (Cell $cell) => $cell->coordinates->x === $number->value)),
            $number,
        );
    }
}
