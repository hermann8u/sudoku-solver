<?php

declare(strict_types=1);

namespace Sudoku\Solver\Method;

use Sudoku\DataStructure\Map;
use Sudoku\Grid;
use Sudoku\Grid\Cell\FillableCell;
use Sudoku\Solver\Association;
use Sudoku\Solver\Association\AssociationExtractor;
use Sudoku\Solver\Candidates;
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

    /**
     * @inheritdoc
     */
    public function apply(Map $candidatesByCell, Grid $grid, FillableCell $currentCell): Map
    {
        $associations = $this->getAllAssociationsInCellGroups($candidatesByCell, $grid, $currentCell);

        /** @var Association $association */
        foreach ($associations as $association) {
            foreach ($association->group->getEmptyCells() as $cell) {
                if ($association->contains($cell)) {
                    $previousCandidates = $candidatesByCell->get($cell);

                    if ($previousCandidates->count() > $association->candidates->count()) {
                        $candidatesByCell = $candidatesByCell->with($cell, $association->candidates);
                    }

                    continue;
                }

                $candidatesByCell = $candidatesByCell->with(
                    $cell,
                    $candidatesByCell->get($cell)->withRemovedValues(...$association->candidates->values),
                );
            }
        }

        return $candidatesByCell;
    }

    /**
     * @param Map<FillableCell, Candidates> $candidatesByCell
     *
     * @return iterable<Association>
     */
    private function getAllAssociationsInCellGroups(Map $candidatesByCell, Grid $grid, FillableCell $cell): iterable
    {
        foreach ($this->extractors as $extractor) {
            foreach ($grid->getGroupsForCell($cell) as $group) {
                // We don't need to identify association in groups with less remaining cells than association count
                if ($group->getEmptyCells()->count() < $extractor::getAssociationType()::getAssociationCount() + 1) {
                    continue;
                }

                yield from $extractor->getAssociationsInGroup($candidatesByCell, $group);
            }
        }
    }
}
