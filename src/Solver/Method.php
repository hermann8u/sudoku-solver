<?php

namespace SudokuSolver\Solver;

use SudokuSolver\Grid\Cell\FillableCell;
use SudokuSolver\Grid\Grid;

interface Method
{
    public static function getName(): string;

    public function apply(CellCandidatesMap $map, Grid $grid, FillableCell $currentCell): CellCandidatesMap;
}
