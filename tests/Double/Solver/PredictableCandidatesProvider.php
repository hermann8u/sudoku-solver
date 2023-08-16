<?php

declare(strict_types=1);

namespace SudokuSolver\Tests\Double\Solver;

use SudokuSolver\Grid\Cell\FillableCell;
use SudokuSolver\Grid\Grid;
use SudokuSolver\Solver\Candidates;
use SudokuSolver\Solver\CandidatesProvider;

final class PredictableCandidatesProvider implements CandidatesProvider
{
    public function __construct(
        private array $candidatesByCoordinatesStrings,
    ) {
    }

    public function getCandidates(Grid $grid, FillableCell $cell): Candidates
    {
        return $this->candidatesByCoordinatesStrings[$cell->coordinates->toString()];
    }

    public function setCandidatesForCell(FillableCell $cell, Candidates $candidates): void
    {
        $this->candidatesByCoordinatesStrings[$cell->coordinates->toString()] = $candidates;
    }
}
