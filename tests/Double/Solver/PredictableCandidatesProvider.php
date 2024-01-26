<?php

declare(strict_types=1);

namespace Sudoku\Tests\Double\Solver;

use Sudoku\Grid;
use Sudoku\Grid\Cell\FillableCell;
use Sudoku\Solver\Candidates;
use Sudoku\Solver\CandidatesProvider;

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
