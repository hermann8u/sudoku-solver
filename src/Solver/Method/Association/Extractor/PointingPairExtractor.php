<?php

declare(strict_types=1);

namespace Sudoku\Solver\Method\Association\Extractor;

use Sudoku\DataStructure\ArrayList;
use Sudoku\DataStructure\Map;
use Sudoku\Grid;
use Sudoku\Grid\Cell\FillableCell;
use Sudoku\Grid\Group;
use Sudoku\Solver\Association\PointingPair;
use Sudoku\Solver\Candidates;
use Sudoku\Solver\Method\Association\AssociationExtractor;

/**
 * @implements AssociationExtractor<PointingPair>
 */
final readonly class PointingPairExtractor implements AssociationExtractor
{
    public static function getAssociationType(): string
    {
        return PointingPair::class;
    }

    /**
     * @inheritdoc
     */
    public function getAssociationWithCell(Map $candidatesByCell, Grid $grid, FillableCell $currentCell): iterable
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
                static fn(Map $carry, FillableCell $relatedCell) => $carry->with(
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
    ): iterable
    {
        $candidatesToRemove = $group->getEmptyCellsNotInGroup($pointingOn)->reduce(
            static fn(Candidates $carry, FillableCell $otherCell) => $carry->merge($candidatesByCell->get($otherCell)),
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
