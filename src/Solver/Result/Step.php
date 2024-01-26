<?php

declare(strict_types=1);

namespace Sudoku\Solver\Result;

use Sudoku\DataStructure\Map;
use Sudoku\Grid\Cell\Coordinates;
use Sudoku\Grid\Cell\FillableCell;
use Sudoku\Grid\Cell\Value;
use Sudoku\Solver\Candidates;

final readonly class Step
{
    /**
     * @param Map<FillableCell, Candidates> $candidatesByCell
     */
    public function __construct(
        public int $number,
        public string $methodName,
        public Map $candidatesByCell,
        public Coordinates $coordinates,
        public Value $value,
    ) {
    }

    public static function fromSolution(int $number, Solution $solution): self
    {
        return new self(
            $number,
            $solution->method,
            $solution->candidatesByCell,
            $solution->cell->coordinates,
            $solution->value,
        );
    }
}
