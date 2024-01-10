<?php

declare(strict_types=1);

namespace SudokuSolver\Grid\Group;

use SudokuSolver\Grid\Group;
use SudokuSolver\Grid\Group\Number\RowNumber;

/**
 * @extends Group<RowNumber>
 */
final readonly class Row extends Group
{
    public static function getNumberType(): string
    {
        return RowNumber::class;
    }
}
