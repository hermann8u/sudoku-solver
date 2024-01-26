<?php

declare(strict_types=1);

namespace Sudoku\Grid\Group;

use Sudoku\Grid\Group;
use Sudoku\Grid\Group\Number\ColumnNumber;

/**
 * @extends Group<ColumnNumber>
 */
final readonly class Column extends Group
{
    public static function getNumberType(): string
    {
        return ColumnNumber::class;
    }
}
