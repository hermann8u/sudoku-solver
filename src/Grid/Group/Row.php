<?php

declare(strict_types=1);

namespace SudokuSolver\Grid\Group;

use SudokuSolver\Grid\Cell;
use SudokuSolver\Grid\Cell\Coordinates;
use SudokuSolver\Grid\Group;
use SudokuSolver\Grid\Group\Number\RowNumber;

final readonly class Row extends Group
{
    /**
     * @param Cell[] $cells
     */
    private function __construct(
        array $cells,
        RowNumber $number,
    ) {
        parent::__construct($cells, $number);
    }

    /**
     * @param Cell[] $cells
     */
    public static function fromAllCells(array $cells, RowNumber $number): self
    {
        return new self(
            array_values(array_filter($cells, static fn (Cell $cell) => $cell->coordinates->y === $number->value)),
            $number
        );
    }
}
