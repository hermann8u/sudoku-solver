<?php

declare(strict_types=1);

namespace SudokuSolver\Grid\Group\Number;

use SudokuSolver\Grid\Cell;
use SudokuSolver\Grid\Cell\Coordinates;
use SudokuSolver\Grid\Group\Number;

final readonly class RowNumber extends Number
{
    public static function fromCell(Cell $cell): static
    {
        return self::fromCoordinates($cell->coordinates);
    }

    public static function fromCoordinates(Coordinates $coordinates): static
    {
        return new self($coordinates->y);
    }
}
