<?php

declare(strict_types=1);

namespace SudokuSolver\Solver\Method;

use SudokuSolver\Grid\Cell\FillableCell;
use SudokuSolver\Grid\Grid;
use SudokuSolver\Solver\Candidates;
use SudokuSolver\Solver\CellCandidatesMap;
use SudokuSolver\Solver\Method;

final readonly class ExclusiveMethod implements Method
{
    public function __construct(
        private InclusiveMethod $inclusiveMethod,
    ) {
    }

    public function apply(CellCandidatesMap $map, Grid $grid, FillableCell $currentCell): CellCandidatesMap
    {
        [$map, $initialCandidates] = $this->getCandidates($map, $grid, $currentCell);

        foreach ($grid->getGroupForCell($currentCell) as $group) {
            $candidates = $initialCandidates;

            foreach ($group->getEmptyCells() as $relatedCell) {
                if ($relatedCell->is($currentCell)) {
                    continue;
                }

                [$map, $relatedCellCandidates] = $this->getCandidates($map, $grid, $relatedCell);

                // Short circuit : When related cell has only one candidate
                if ($relatedCellCandidates->hasUniqueValue()) {
                    return $map->merge($relatedCell, $relatedCellCandidates);
                }

                $candidates = $candidates->withRemovedValues(...$relatedCellCandidates);
            }

            if ($candidates->hasUniqueValue()) {
                return $map->merge($currentCell, $candidates);
            }
        }

        return $map;
    }

    /**
     * @return array{CellCandidatesMap, Candidates}
     */
    private function getCandidates(CellCandidatesMap $map, Grid $grid, FillableCell $cell): array
    {
        if (! $map->has($cell)) {
            $map = $this->inclusiveMethod->apply($map, $grid, $cell);
        }

        return [$map, $map->get($cell)];
    }
}
