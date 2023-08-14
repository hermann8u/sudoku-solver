<?php

declare(strict_types=1);

namespace SudokuSolver\Solver\Method;

use SudokuSolver\Grid\Cell\FillableCell;
use SudokuSolver\Grid\Grid;
use SudokuSolver\Grid\Group;
use SudokuSolver\Solver\Association\AssociationExtractor;
use SudokuSolver\Solver\CellCandidatesMap;
use SudokuSolver\Solver\Method;

final readonly class ExclusiveAssociationMethod implements Method
{
    /**
     * @param InclusiveMethod $inclusiveMethod
     * @param iterable<AssociationExtractor> $extractors
     */
    public function __construct(
        private InclusiveMethod $inclusiveMethod,
        private iterable $extractors,
    ) {
    }

    public function apply(CellCandidatesMap $map, Grid $grid, FillableCell $currentCell): CellCandidatesMap
    {
        foreach ($grid->getGroupForCell($currentCell) as $group) {
            [$map, $mapForGroup] = $this->getMapForGroup($map, $grid, $group);

            foreach ($this->extractors as $extractor) {
                $associations = $extractor->getAssociationsForGroup($mapForGroup);

                foreach ($associations as $association) {
                    foreach ($group->getEmptyCells() as $cell) {
                        if ($association->contains($cell)) {
                            continue;
                        }

                        $candidates = $map->get($cell);
                        $candidates = $candidates->withRemovedValues(...$association->candidates);

                        $map = $map->merge($cell, $candidates);

                        if ($candidates->hasUniqueValue()) {
                            return $map;
                        }
                    }
                }
            }
        }

        return $map;
    }

    /**
     * @return array{CellCandidatesMap, CellCandidatesMap}
     */
    private function getMapForGroup(CellCandidatesMap $map, Grid $grid, Group $group): array
    {
        $partialMap = CellCandidatesMap::empty();

        foreach ($group->getEmptyCells() as $cell) {
            if (! $map->has($cell)) {
                $map = $this->inclusiveMethod->apply($map, $grid, $cell);
            }

            $partialMap = $partialMap->merge($cell, $map->get($cell));
        }

        return [$map, $partialMap];
    }
}
