<?php

declare(strict_types=1);

namespace Florian\SudokuSolver\Solver\Method;

use Florian\SudokuSolver\Grid\Cell\FillableCell;
use Florian\SudokuSolver\Grid\Grid;
use Florian\SudokuSolver\Solver\Candidates;
use Florian\SudokuSolver\Solver\CellCandidatesMap;
use Florian\SudokuSolver\Solver\Method;

final readonly class UniqueCandidateComparedToRelatedCellsMethod implements Method
{
    public function __construct(
        private ObviousCandidateMethod $obviousCandidateMethod,
    ) {
    }

    public function apply(Grid $grid, FillableCell $currentCell, CellCandidatesMap $map): CellCandidatesMap
    {
        [$map, $initialCandidates] = $this->getCandidates($grid, $currentCell, $map);

        foreach ($grid->getSetsOfCell($currentCell) as $set) {
            $candidates = $initialCandidates;

            foreach ($set->getEmptyCells() as $relatedCell) {
                if ($relatedCell->is($currentCell)) {
                    continue;
                }

                [$map, $relatedCellCandidates] = $this->getCandidates($grid, $relatedCell, $map);

                // Short circuit :  When related cell has only one candidate
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
    private function getCandidates(Grid $grid, FillableCell $cell, CellCandidatesMap $map): array
    {
        if (! $map->has($cell)) {
            $map = $this->obviousCandidateMethod->apply($grid, $cell, $map);
        }

        return [$map, $map->get($cell)];
    }
}
