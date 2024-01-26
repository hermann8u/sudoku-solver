<?php

namespace Sudoku\Solver;

use Sudoku\Grid;
use Sudoku\Grid\Cell\FillableCell;

interface Method
{
    public static function getName(): string;

    public function apply(CellCandidatesMap $map, Grid $grid, FillableCell $currentCell): CellCandidatesMap;
}
