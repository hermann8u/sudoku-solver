<?php

declare(strict_types=1);

namespace Sudoku\Solver\Result;

use Sudoku\Grid\Cell\Coordinates;
use Sudoku\Grid\Cell\Value;
use Sudoku\Solver\CellCandidatesMap;

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
