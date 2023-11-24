<?php

declare(strict_types=1);

namespace SudokuSolver\Solver\Result;

use SudokuSolver\Grid\Cell\Coordinates;
use SudokuSolver\Grid\Cell\Value;

final readonly class Step
{
    public function __construct(
        public int $number,
        public string $methodName,
        public Coordinates $coordinates,
        public Value $value,
    ) {
    }

    public static function fromSolution(int $number, Solution $solution): self
    {
        return new self(
            $number,
            $solution->method,
            $solution->cell->coordinates,
            $solution->value,
        );
    }
}
