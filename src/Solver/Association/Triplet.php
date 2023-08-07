<?php

declare(strict_types=1);

namespace Florian\SudokuSolver\Solver\Association;

use Florian\SudokuSolver\Solver\Association;

final readonly class Triplet extends Association
{
    public const COUNT = 3;

    public static function getAssociationCount(): int
    {
        return self::COUNT;
    }
}
