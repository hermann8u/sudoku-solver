<?php

declare(strict_types=1);

namespace Florian\SudokuSolver\Solver\Method;

use Florian\SudokuSolver\Grid\Cell\FillableCell;
use Florian\SudokuSolver\Grid\Grid;
use Florian\SudokuSolver\Solver\Candidates;
use Florian\SudokuSolver\Solver\CellCandidatesMap;
use Florian\SudokuSolver\Solver\Method;

final class InclusiveMethod implements Method
{
    public function apply(CellCandidatesMap $map, Grid $grid, FillableCell $currentCell): CellCandidatesMap
    {
        if (! $map->has($currentCell)) {
            $map = $this->getCandidates($map, $grid, $currentCell);
        }

        return $map;
    }

    private function getCandidates(CellCandidatesMap $map, Grid $grid, FillableCell $currentCell): CellCandidatesMap
    {
        $groups = $grid->getGroupForCell($currentCell);

        $candidatesByGroup = [];

        foreach ($groups as $group) {
            $candidates = Candidates::all()->withRemovedValues(...$group->getPresentValues());

            if ($candidates->hasUniqueValue()) {
                return $map->merge($currentCell, $candidates);
            }

            $candidatesByGroup[] = $candidates;
        }

        return $map->merge($currentCell, Candidates::intersect(...$candidatesByGroup));
    }
}
