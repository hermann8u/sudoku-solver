<?php

declare(strict_types=1);

namespace Florian\SudokuSolver\Solver\Association;

use Florian\SudokuSolver\Solver\Association;

final readonly class Pair extends Association
{
    public const COUNT = 2;

    public static function getAssociationCount(): int
    {
        return self::COUNT;
    }
}
