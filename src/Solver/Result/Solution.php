<?php

declare(strict_types=1);

namespace SudokuSolver\Solver\Result;

use SudokuSolver\Grid\Cell\FillableCell;
use SudokuSolver\Grid\Cell\Value;
use SudokuSolver\Solver\CellCandidatesMap;

final readonly class Solution
{
    public function __construct(
        public string $method,
        public CellCandidatesMap $map,
        public FillableCell $cell,
        public Value $value,
    ) {
    }
}
