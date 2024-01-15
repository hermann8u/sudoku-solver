<?php

declare(strict_types=1);

namespace SudokuSolver\Solver\Method;

use SudokuSolver\Grid\Cell\FillableCell;
use SudokuSolver\Grid\Grid;
use SudokuSolver\Solver\CellCandidatesMap;
use SudokuSolver\Solver\Method;

final readonly class ExclusiveMethod implements Method
{
    public static function getName(): string
    {
        return 'exclusive';
    }

    public function apply(CellCandidatesMap $map, Grid $grid, FillableCell $currentCell): CellCandidatesMap
    {
        $initialCandidates = $map->get($currentCell);

        foreach ($grid->getGroupsForCell($currentCell) as $group) {
            $candidates = $initialCandidates;

            foreach ($group->getEmptyCells() as $relatedCell) {
                if ($relatedCell->is($currentCell)) {
                    continue;
                }

                $candidates = $candidates->withRemovedValues(...$map->get($relatedCell)->values);
            }

            if ($candidates->hasUniqueCandidate()) {
                return $map->with($currentCell, $candidates);
            }
        }

        return $map;
    }
}
