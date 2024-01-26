<?php

declare(strict_types=1);

namespace Sudoku\Solver\Method;

use Sudoku\DataStructure\ArrayList;
use Sudoku\Grid;
use Sudoku\Grid\Cell\FillableCell;
use Sudoku\Solver\Association;
use Sudoku\Solver\Association\AssociationExtractor;
use Sudoku\Solver\CellCandidatesMap;
use Sudoku\Solver\Method;

final readonly class ExclusiveAssociationMethod implements Method
{
    /**
     * @param iterable<AssociationExtractor> $extractors
     */
    public function __construct(
        private iterable $extractors,
    ) {
    }

    public static function getName(): string
    {
        return 'exclusive_association';
    }

    public function apply(CellCandidatesMap $map, Grid $grid, FillableCell $currentCell): CellCandidatesMap
    {
        $associations = $this->getAllAssociationsInCellGroups($map, $grid, $currentCell);

        /** @var Association $association */
        foreach ($associations as $association) {
            foreach ($association->group->getEmptyCells() as $cell) {
                if ($association->contains($cell)) {
                    continue;
                }

                $candidates = $map->get($cell);
                $candidates = $candidates->withRemovedValues(...$association->candidates->values);

                $map = $map->with($cell, $candidates);

                if ($candidates->hasUniqueCandidate()) {
                    return $map;
                }
            }
        }

        return $map;
    }

    /**
     * @return ArrayList<Association>
     */
    private function getAllAssociationsInCellGroups(CellCandidatesMap $map, Grid $grid, FillableCell $cell): ArrayList
    {
        /** @var ArrayList<Association> $associations */
        $associations = ArrayList::empty();

        foreach ($this->extractors as $extractor) {
            foreach ($grid->getGroupsForCell($cell) as $group) {
                // We don't need to identify association in groups with less remaining cells than association count
                if ($group->getEmptyCells()->count() < $extractor::getAssociationType()::getAssociationCount() + 1) {
                    continue;
                }

                $associations = $associations->merge($extractor->getAssociationsInGroup($map, $group));
            }
        }

        return $associations;
    }
}
