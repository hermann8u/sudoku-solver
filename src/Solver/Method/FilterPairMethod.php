<?php

declare(strict_types=1);

namespace Florian\SudokuSolver\Solver\Method;

use Florian\SudokuSolver\Grid\Cell\FillableCell;
use Florian\SudokuSolver\Grid\Grid;
use Florian\SudokuSolver\Solver\Candidates;
use Florian\SudokuSolver\Solver\CellCandidatesMap;
use Florian\SudokuSolver\Solver\Method;

final readonly class FilterPairMethod implements Method
{
    public function __construct(
        private ObviousCandidateMethod $obviousCandidateMethod,
    ) {
    }

    public function apply(Grid $grid, FillableCell $currentCell, CellCandidatesMap $map): CellCandidatesMap
    {
        $sets = $grid->getSetsOfCell($currentCell);

        foreach ($sets as $set) {
            $candidateCounts = array_fill(1, 9, []);

            foreach ($set->getEmptyCells() as $cell) {
                [$map, $candidates] = $this->getCandidates($grid, $cell, $map);

                foreach ($candidates as $candidate) {
                    $candidateCounts[$candidate->value][] = $cell->coordinates->toString();
                }
            }

            $candidateCounts = array_filter($candidateCounts, static fn (array $items) => count($items) === 2);

            foreach ($set->getEmptyCells() as $cell) {
                $newCandidates = [];

                foreach ($candidateCounts as $value => $cellsCoordinates) {
                    if (in_array($cell->coordinates->toString(), $cellsCoordinates, true)) {
                        $newCandidates[] = (int) $value;
                    }
                }

                if (count($newCandidates) > 1) {
                    $map = $map->merge($cell, Candidates::fromInt(...$newCandidates));
                }
            }
        }

        [$map] = $this->getCandidates($grid, $currentCell, $map);

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
