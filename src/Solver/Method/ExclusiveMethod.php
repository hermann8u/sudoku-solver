<?php

declare(strict_types=1);

namespace Sudoku\Solver\Method;

use Sudoku\Grid;
use Sudoku\Grid\Cell\FillableCell;
use Sudoku\Solver\CellCandidatesMap;
use Sudoku\Solver\Method;

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
