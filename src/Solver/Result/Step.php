<?php

declare(strict_types=1);

namespace Sudoku\Solver\Result;

use Sudoku\DataStructure\Map;
use Sudoku\Grid\Cell\FillableCell;
use Sudoku\Solver\Candidates;

final readonly class Step
{
    /**
     * @param ?Map<FillableCell, Candidates> $candidatesByCell
     */
    public function __construct(
        public int $number,
        public ?Map $candidatesByCell,
        public ?Solution $solution,
    ) {
    }
}
