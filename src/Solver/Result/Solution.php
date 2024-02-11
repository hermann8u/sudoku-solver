<?php

declare(strict_types=1);

namespace Sudoku\Solver\Result;

use Sudoku\Grid\Cell\FillableCell;
use Sudoku\Grid\Cell\Value;

final readonly class Solution
{
    public function __construct(
        public string $method,
        public FillableCell $cell,
        public Value $value,
    ) {
    }
}
