<?php

declare(strict_types=1);

namespace SudokuSolver\Solver\Method;

use SudokuSolver\Grid\Cell\FillableCell;
use SudokuSolver\Grid\Grid;
use SudokuSolver\Solver\Candidates;
use SudokuSolver\Solver\CellCandidatesMap;
use SudokuSolver\Solver\Method;

final readonly class InclusiveMethod implements Method
{
    public static function getName(): string
    {
        return 'inclusive';
    }

    public function apply(CellCandidatesMap $map, Grid $grid, FillableCell $currentCell): CellCandidatesMap
    {
        if (! $map->has($currentCell)) {
            $map = $this->getCandidates($map, $grid, $currentCell);
        }

        return $map;
    }

    private function getCandidates(CellCandidatesMap $map, Grid $grid, FillableCell $currentCell): CellCandidatesMap
    {
        $groups = $grid->getGroupsForCell($currentCell);

        $candidatesByGroup = [];

        foreach ($groups as $group) {
            $candidates = Candidates::all()->withRemovedValues(...$group->getPresentValues());

            if ($candidates->hasUniqueValue()) {
                return $map->merge($currentCell, $candidates);
            }

            $candidatesByGroup[] = $candidates;
        }

        return $map->merge($currentCell, Candidates::fromIntersect(...$candidatesByGroup));
    }
}
