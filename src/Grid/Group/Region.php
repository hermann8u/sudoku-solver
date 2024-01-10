<?php

declare(strict_types=1);

namespace SudokuSolver\Grid\Group;

use SudokuSolver\Grid\Group;
use SudokuSolver\Grid\Group\Number\RegionNumber;

/**
 * @extends Group<RegionNumber>
 */
final readonly class Region extends Group
{
    public const WIDTH = 3;
    public const HEIGHT = 3;

    public static function getNumberType(): string
    {
        return RegionNumber::class;
    }
}
