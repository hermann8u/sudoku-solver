<?php

namespace Sudoku\Solver;

use Sudoku\DataStructure\Map;
use Sudoku\Grid;
use Sudoku\Grid\Cell\FillableCell;

interface Method
{
    public static function getName(): string;

    /**
     * @param Map<FillableCell, Candidates> $candidatesByCell
     *
     * @return Map<FillableCell, Candidates> The updated $candidatesByCell map
     */
    public function apply(Map $candidatesByCell, Grid $grid, FillableCell $currentCell): Map;
}
