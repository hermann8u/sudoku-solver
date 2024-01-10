<?php

declare(strict_types=1);

namespace SudokuSolver\Solver\Result;

use SudokuSolver\Grid\Cell\Coordinates;
use SudokuSolver\Grid\Cell\Value;
use SudokuSolver\Solver\CellCandidatesMap;

final readonly class Step
{
    public function __construct(
        public int $number,
        public string $methodName,
        public CellCandidatesMap $map,
        public Coordinates $coordinates,
        public Value $value,
    ) {
    }

    public static function fromSolution(int $number, Solution $solution): self
    {
        return new self(
            $number,
            $solution->method,
            $solution->map,
            $solution->cell->coordinates,
            $solution->value,
        );
    }
}
