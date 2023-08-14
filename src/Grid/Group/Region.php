<?php

declare(strict_types=1);

namespace SudokuSolver\Grid\Group;

use SudokuSolver\Grid\Cell;
use SudokuSolver\Grid\Group;
use SudokuSolver\Grid\Group\Number\RegionNumber;

final readonly class Region extends Group
{
    public const WIDTH = 3;
    public const HEIGHT = 3;

    private function __construct(
        array $cells,
        RegionNumber $number,
    ) {
        parent::__construct($cells, $number);
    }

    /**
     * @param Cell[] $cells
     */
    public static function fromAllCells(array $cells, RegionNumber $number): self
    {
        return new self(
            array_values(array_filter($cells, static fn (Cell $cell) => $cell->regionNumber->value === $number->value)),
            $number,
        );
    }
}
