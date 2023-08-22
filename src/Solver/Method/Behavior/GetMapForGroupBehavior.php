<?php

declare(strict_types=1);

namespace SudokuSolver\Solver\Method\Behavior;

use SudokuSolver\Grid\Grid;
use SudokuSolver\Grid\Group;
use SudokuSolver\Solver\CandidatesProvider;
use SudokuSolver\Solver\CellCandidatesMap;

trait GetMapForGroupBehavior
{
    private readonly CandidatesProvider $candidatesProvider;

    /**
     * @return array{CellCandidatesMap, CellCandidatesMap}
     */
    private function getMapForGroup(CellCandidatesMap $map, Grid $grid, Group $group): array
    {
        $partialMap = CellCandidatesMap::empty();

        foreach ($group->getEmptyCells() as $cell) {
            if (! $map->has($cell)) {
                $map = $map->merge($cell, $this->candidatesProvider->getCandidates($grid, $cell));
            }

            $partialMap = $partialMap->merge($cell, $map->get($cell));
        }

        return [$map, $partialMap];
    }

}
