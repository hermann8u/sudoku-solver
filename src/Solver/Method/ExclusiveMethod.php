<?php

declare(strict_types=1);

namespace SudokuSolver\Solver\Method;

use SudokuSolver\Grid\Cell\FillableCell;
use SudokuSolver\Grid\Grid;
use SudokuSolver\Solver\CandidatesProvider;
use SudokuSolver\Solver\CellCandidatesMap;
use SudokuSolver\Solver\Method;
use SudokuSolver\Solver\Method\Behavior\GetCandidatesBehavior;

final readonly class ExclusiveMethod implements Method
{
    use GetCandidatesBehavior;

    public function __construct(
        private CandidatesProvider $candidatesProvider,
    ) {
    }

    public static function getName(): string
    {
        return 'exclusive';
    }

    public function apply(CellCandidatesMap $map, Grid $grid, FillableCell $currentCell): CellCandidatesMap
    {
        [$map, $initialCandidates] = $this->getCandidates($map, $grid, $currentCell);

        foreach ($grid->getGroupsForCell($currentCell) as $group) {
            $candidates = $initialCandidates;

            foreach ($group->getEmptyCells() as $relatedCell) {
                if ($relatedCell->is($currentCell)) {
                    continue;
                }

                [$map, $relatedCellCandidates] = $this->getCandidates($map, $grid, $relatedCell);

                $candidates = $candidates->withRemovedValues(...$relatedCellCandidates);
            }

            if ($candidates->hasUniqueCandidate()) {
                return $map->merge($currentCell, $candidates);
            }
        }

        return $map;
    }
}
