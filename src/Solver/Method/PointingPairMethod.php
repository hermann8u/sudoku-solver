<?php

declare(strict_types=1);

namespace Sudoku\Solver\Method;

use Sudoku\DataStructure\ArrayList;
use Sudoku\DataStructure\Map;
use Sudoku\Grid;
use Sudoku\Grid\Cell\FillableCell;
use Sudoku\Grid\Cell\Value;
use Sudoku\Grid\Group;
use Sudoku\Solver\Candidates;
use Sudoku\Solver\Method;
use Sudoku\Solver\PointingPair;

final class PointingPairMethod implements Method
{
    public static function getName(): string
    {
        return 'pointing_pair';
    }

    public function apply(Map $candidatesByCell, Grid $grid, FillableCell $currentCell): Map
    {
        $pointingPairs = $this->getPointingPairsWithCell($candidatesByCell, $grid, $currentCell);

        /** @var PointingPair $pointingPair */
        foreach ($pointingPairs as $pointingPair) {
            /** @var FillableCell $cell */
            foreach ($pointingPair->getCellToUpdate() as $cell) {
                /** @var Candidates $candidates */
                $candidates = $candidatesByCell->get($cell);

                if (! $candidates->values->exists(static fn (Value $v) => $v->equals($pointingPair->value))) {
                    continue;
                }

                $candidatesByCell = $candidatesByCell->with(
                    $cell,
                    $candidates->withRemovedValues($pointingPair->value),
                );
            }
        }

        return $candidatesByCell;
    }

    /**
     * @param Map<FillableCell, Candidates> $candidatesByCell
     *
     * @return iterable<PointingPair>
     */
    private function getPointingPairsWithCell(Map $candidatesByCell, Grid $grid, FillableCell $currentCell): iterable
    {
        $currentCellCandidates = $candidatesByCell->get($currentCell);

        $region = $grid->getRegionByCell($currentCell);
        /** @var Group[] $otherGroups */
        $otherGroups = [$grid->getRowByCell($currentCell), $grid->getColumnByCell($currentCell)];

        foreach ($otherGroups as $group) {
            $relatedCell = $group
                ->getEmptyCellsInGroup($region)
                ->filter($currentCell->isNot(...));

            /** @var Map<FillableCell, Candidates> $intersectCandidatesByRelatedCell */
            $intersectCandidatesByRelatedCell = $relatedCell->reduce(
                static fn (Map $carry, FillableCell $relatedCell) => $carry->with(
                    $relatedCell,
                    $currentCellCandidates->intersect($candidatesByCell->get($relatedCell)),
                ),
                Map::empty(),
            );

            yield from $this->buildPointingPairs(
                $candidatesByCell,
                $intersectCandidatesByRelatedCell,
                $group,
                $region,
                $currentCell,
            );

            yield from $this->buildPointingPairs(
                $candidatesByCell,
                $intersectCandidatesByRelatedCell,
                $region,
                $group,
                $currentCell,
            );
        }
    }

    /**
     * @param Map<FillableCell, Candidates> $candidatesByCell
     * @param Map<FillableCell, Candidates> $intersectCandidatesByRelatedCell
     *
     * @return iterable<PointingPair>
     */
    private function buildPointingPairs(
        Map $candidatesByCell,
        Map $intersectCandidatesByRelatedCell,
        Group $group,
        Group $pointingOn,
        FillableCell $currentCell,
    ): iterable {
        $candidatesToRemove = $group->getEmptyCellsNotInGroup($pointingOn)->reduce(
            static fn (Candidates $carry, FillableCell $otherCell) => $carry->merge($candidatesByCell->get($otherCell)),
            Candidates::empty(),
        );

        if ($candidatesToRemove->count() === 0) {
            return;
        }

        foreach ($intersectCandidatesByRelatedCell as $relatedCell => $intersectCandidates) {
            $candidates = $intersectCandidates->withRemovedValues(...$candidatesToRemove->values);

            foreach ($candidates->values as $value) {
                yield new PointingPair($group, $pointingOn, ArrayList::fromItems($currentCell, $relatedCell), $value);
            }
        }
    }
}
