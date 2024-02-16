<?php

declare(strict_types=1);

namespace Sudoku\Solver;

use Sudoku\DataStructure\ArrayList;
use Sudoku\Grid;
use Sudoku\Grid\Cell\FillableCell;
use Sudoku\Grid\Cell\Value;

interface Association
{
    /**
     * @return ArrayList<FillableCell>
     */
    public function getTargetedCells(Grid $grid): ArrayList;

    /**
     * @return ArrayList<Value>
     */
    public function getCandidatesToEliminate(): ArrayList;
}
