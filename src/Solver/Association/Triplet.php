<?php

declare(strict_types=1);

namespace Florian\SudokuSolver\Solver\Association;

use Florian\SudokuSolver\Solver\Association;

final readonly class Triplet extends Association
{
    protected function getAssociationCount(): int
    {
        return 3;
    }
}
