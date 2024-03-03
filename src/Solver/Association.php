<?php

declare(strict_types=1);

namespace Sudoku\Solver;

use Stringable;
use Sudoku\DataStructure\ArrayList;
use Sudoku\Grid;
use Sudoku\Grid\Cell\FillableCell;
use Sudoku\Grid\Cell\Value;

interface Association extends Stringable
{
    /**
     * @return ArrayList<FillableCell>
     */
    public function getTargetedCells(Grid $grid): ArrayList;

    /**
     * @return ArrayList<Value>
     */
    public function getCandidatesToEliminate(): ArrayList;

    public function toString(): string;
}
