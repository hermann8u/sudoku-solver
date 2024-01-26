<?php

declare(strict_types=1);

namespace Sudoku\Solver\Result;

use Sudoku\DataStructure\Map;
use Sudoku\Grid\Cell\FillableCell;
use Sudoku\Grid\Cell\Value;
use Sudoku\Solver\Candidates;

final readonly class Solution
{
    /**
     * @param Map<FillableCell, Candidates> $candidatesByCell
     */
    public function __construct(
        public string $method,
        public Map $candidatesByCell,
        public FillableCell $cell,
        public Value $value,
    ) {
    }
}
