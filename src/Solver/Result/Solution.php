<?php

declare(strict_types=1);

namespace Sudoku\Solver\Result;

use Sudoku\Grid\Cell\FillableCell;
use Sudoku\Grid\Cell\Value;
use Sudoku\Solver\CellCandidatesMap;

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
