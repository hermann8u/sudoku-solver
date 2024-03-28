<?php

declare(strict_types=1);

namespace Sudoku\Solver\Result;

use Sudoku\DataStructure\Map;
use Sudoku\Grid;
use Sudoku\Grid\Cell\FillableCell;
use Sudoku\Solver\Candidates;

final readonly class Step
{
    /**
     * @param ?Map<FillableCell, Candidates> $candidatesByCell
     */
    private function __construct(
        public ?Solution $solution,
        public ?Map $candidatesByCell,
    ) {
    }

    /**
     * @param ?Map<FillableCell, Candidates> $candidatesByCell
     */
    public static function fromSolution(Solution $solution, ?Map $candidatesByCell = null): self
    {
        return new self($solution, $candidatesByCell);
    }

    /**
     * @param Map<FillableCell, Candidates> $candidatesByCell
     */
    public static function fromNoSolution(Map $candidatesByCell): self
    {
        return new self(null, $candidatesByCell);
    }

    public function applyOn(Grid $grid): Grid
    {
        if ($this->solution === null) {
            return $grid;
        }

        return $grid->withUpdatedCell($this->solution->updatedCell);
    }
}
