<?php

declare(strict_types=1);

namespace SudokuSolver\Solver\Result;

use SudokuSolver\Grid\Cell\CellValue;
use SudokuSolver\Grid\Cell\Coordinates;

final readonly class Step
{
    public function __construct(
        public int $number,
        public string $methodName,
        public Coordinates $coordinates,
        public CellValue $value,
    ) {
    }
}
