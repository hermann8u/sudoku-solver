<?php

declare(strict_types=1);

namespace Sudoku\Grid\Group;

use Sudoku\Grid\Group;
use Sudoku\Grid\Group\Number\RowNumber;

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
