<?php

declare(strict_types=1);

namespace Sudoku\Solver\Association;

final readonly class Pair extends NakedAssociation
{
    public const COUNT = 2;

    public static function getAssociationCount(): int
    {
        return self::COUNT;
    }
}
