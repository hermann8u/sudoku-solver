<?php

declare(strict_types=1);

namespace Sudoku\Solver\Association;

use Sudoku\Solver\Association;

final readonly class Triplet extends Association
{
    public const COUNT = 3;

    public static function getAssociationCount(): int
    {
        return self::COUNT;
    }
}
