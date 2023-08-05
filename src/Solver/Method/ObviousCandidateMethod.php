<?php

declare(strict_types=1);

namespace Florian\SudokuSolver\Solver\Method;

use Florian\SudokuSolver\Grid\Cell\FillableCell;
use Florian\SudokuSolver\Grid\Grid;
use Florian\SudokuSolver\Solver\Candidates;
use Florian\SudokuSolver\Solver\CellCandidatesMap;
use Florian\SudokuSolver\Solver\Method;

final class ObviousCandidateMethod implements Method
{
    public function apply(CellCandidatesMap $map, Grid $grid, FillableCell $currentCell): CellCandidatesMap
    {
        $sets = $grid->getSetsOfCell($currentCell);

        $candidatesBySet = [];

        foreach ($sets as $set) {
            $candidates = Candidates::all()->withRemovedValues(...$set->getPresentValues());

            if ($candidates->hasUniqueValue()) {
                return $map->merge($currentCell, $candidates);
            }

            $candidatesBySet[] = $candidates;
        }

        return $map->merge($currentCell, Candidates::intersect(...$candidatesBySet));
    }
}
