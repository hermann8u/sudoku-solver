<?php

declare(strict_types=1);

namespace SudokuSolver\Solver\Method;

use SudokuSolver\Grid\Cell\FillableCell;
use SudokuSolver\Grid\Grid;
use SudokuSolver\Grid\Group;
use SudokuSolver\Solver\Association;
use SudokuSolver\Solver\Association\AssociationExtractor;
use SudokuSolver\Solver\CandidatesProvider;
use SudokuSolver\Solver\CellCandidatesMap;
use SudokuSolver\Solver\Method;
use SudokuSolver\Solver\Method\Behavior\GetMapForGroupBehavior;

final readonly class ExclusiveAssociationMethod implements Method
{
    use GetMapForGroupBehavior;

    /**
     * @param iterable<AssociationExtractor> $extractors
     */
    public function __construct(
        private CandidatesProvider $candidatesProvider,
        private iterable $extractors,
    ) {
    }

    public static function getName(): string
    {
        return 'exclusive_association';
    }

    public function apply(CellCandidatesMap $map, Grid $grid, FillableCell $currentCell): CellCandidatesMap
    {
        foreach ($grid->getGroupsForCell($currentCell) as $group) {
            [$map, $associations] = $this->getAllAssociationsForGroup($map, $grid, $group);

            foreach ($associations as $association) {
                foreach ($group->getEmptyCells() as $cell) {
                    if ($association->contains($cell)) {
                        continue;
                    }

                    $candidates = $map->get($cell);
                    $candidates = $candidates->withRemovedValues(...$association->candidates);

                    $map = $map->merge($cell, $candidates);

                    if ($candidates->hasUniqueCandidate()) {
                        return $map;
                    }
                }
            }
        }

        return $map;
    }

    /**
     * @return array{CellCandidatesMap, Association[]}
     */
    private function getAllAssociationsForGroup(CellCandidatesMap $map, Grid $grid, Group $group): array
    {
        [$map, $mapForGroup] = $this->getMapForGroup($map, $grid, $group);

        $associations = [];

        foreach ($this->extractors as $extractor) {
            $associations = [...$associations, ...$extractor->getAssociationsForGroup($mapForGroup)];
        }

        return [$map, $associations];
    }
}
