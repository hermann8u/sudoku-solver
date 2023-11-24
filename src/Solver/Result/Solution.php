<?php

declare(strict_types=1);

namespace SudokuSolver\Solver\Result;

use SudokuSolver\Grid\Cell\FillableCell;
use SudokuSolver\Grid\Cell\Value;

final readonly class Solution
{
    public function __construct(
        public string $method,
        public FillableCell $cell,
        public Value $value,
    ) {
    }
}
