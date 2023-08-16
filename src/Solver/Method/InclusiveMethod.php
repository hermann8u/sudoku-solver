<?php

declare(strict_types=1);

namespace SudokuSolver\Solver\Method;

use SudokuSolver\Grid\Cell\FillableCell;
use SudokuSolver\Grid\Grid;
use SudokuSolver\Solver\Candidates;
use SudokuSolver\Solver\CandidatesProvider;
use SudokuSolver\Solver\CellCandidatesMap;
use SudokuSolver\Solver\Method;

final readonly class InclusiveMethod implements Method, CandidatesProvider
{
    public static function getName(): string
    {
        return 'inclusive';
    }

    public function apply(CellCandidatesMap $map, Grid $grid, FillableCell $currentCell): CellCandidatesMap
    {
        if (! $map->has($currentCell)) {
            $map = $map->merge($currentCell, $this->getCandidates($grid, $currentCell));
        }

        return $map;
    }

    public function getCandidates(Grid $grid, FillableCell $cell): Candidates
    {
        $groups = $grid->getGroupsForCell($cell);

        $candidatesByGroup = [];

        foreach ($groups as $group) {
            $candidates = Candidates::all()->withRemovedValues(...$group->getPresentValues());

            if ($candidates->hasUniqueValue()) {
                return $candidates;
            }

            $candidatesByGroup[] = $candidates;
        }

        return Candidates::fromIntersect(...$candidatesByGroup);
    }
}
