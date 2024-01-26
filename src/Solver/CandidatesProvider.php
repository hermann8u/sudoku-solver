<?php

declare(strict_types=1);

namespace Sudoku\Solver;

use Sudoku\Grid;
use Sudoku\Grid\Cell\FillableCell;

interface CandidatesProvider
{
    public function getCandidates(Grid $grid, FillableCell $cell): Candidates;
}
