<?php

namespace Florian\SudokuSolver\Solver;

use Florian\SudokuSolver\Grid\Cell\FillableCell;
use Florian\SudokuSolver\Grid\Grid;

interface Method
{
    public function apply(Grid $grid, FillableCell $currentCell, CellCandidatesMap $map): CellCandidatesMap;
}
