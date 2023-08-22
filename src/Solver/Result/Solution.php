<?php

declare(strict_types=1);

namespace SudokuSolver\Solver\Result;

use SudokuSolver\Grid\Cell\Coordinates;
use SudokuSolver\Grid\Cell\Value;

final readonly class Solution
{
    public function __construct(
        public string $method,
        public Coordinates $coordinates,
        public Value $value,
    ) {
    }
}
