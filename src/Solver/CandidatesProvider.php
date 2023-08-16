<?php

declare(strict_types=1);

namespace SudokuSolver\Solver;

use SudokuSolver\Grid\Cell\FillableCell;
use SudokuSolver\Grid\Grid;

interface CandidatesProvider
{
    public function getCandidates(Grid $grid, FillableCell $cell): Candidates;
}
