<?php

declare(strict_types=1);

namespace Sudoku\Solver\Method\Association\Extractor;

use Sudoku\DataStructure\ArrayList;
use Sudoku\DataStructure\Map;
use Sudoku\Grid;
use Sudoku\Grid\Cell\FillableCell;
use Sudoku\Grid\Cell\Value;
use Sudoku\Grid\Group;
use Sudoku\Solver\Association\YWing;
use Sudoku\Solver\Candidates;
use Sudoku\Solver\Method\Association\AssociationExtractor;

/**
 * @implements AssociationExtractor<YWing>
 */
final class YWingExtractor implements AssociationExtractor
{
    public static function getAssociationType(): string
    {
        return YWing::class;
    }

    public function getAssociationWithCell(Map $candidatesByCell, Grid $grid, FillableCell $currentCell): iterable
    {
        $pivotCandidates = $candidatesByCell->get($currentCell);

        if ($pivotCandidates->count() !== 2) {
            return ArrayList::empty();
        }

        return $grid->getGroupsForCell($currentCell)
            ->map(fn (Group $group) => $this->getCandidatesByPotentialPincerInGroup(
                $candidatesByCell,
                $group,
                $currentCell,
            ))
            ->multidimensionalLoop(
                function (
                    ArrayList $carry,
                    Map $candidatesByPotentialFirstPincers,
                    Map $candidatesByPotentialSecondPincers,
                ) use ($currentCell, $pivotCandidates) {
                    $pincerAssociations = $this->associatePincers(
                        $candidatesByPotentialFirstPincers,
                        $candidatesByPotentialSecondPincers,
                        $pivotCandidates,
                    );

                    foreach ($pincerAssociations as [$firstPincer, $secondPincer, $value]) {
                        $carry = $carry->with(new YWing(
                            $currentCell,
                            ArrayList::fromItems($firstPincer, $secondPincer),
                            $value,
                        ));
                    }

                    return $carry;
                },
                ArrayList::empty(),
            );
    }

    /**
     * @param Map<FillableCell, Candidates> $candidatesByCell
     *
     * @return Map<FillableCell, Candidates>
     */
    private function getCandidatesByPotentialPincerInGroup(
        Map $candidatesByCell,
        Group $group,
        FillableCell $pivot,
    ): Map {
        $pivotCandidates = $candidatesByCell->get($pivot);

        return $group->getEmptyCells()
            ->filter($pivot->isNot(...))
            ->reduce(
                function (Map $carry, FillableCell $pincer) use ($pivotCandidates, $candidatesByCell) {
                    $pincerCandidates = $candidatesByCell->get($pincer);

                    if ($pincerCandidates->count() !== 2) {
                        return $carry;
                    }

                    $intersectCandidates = $pivotCandidates->intersect($pincerCandidates);
                    if ($intersectCandidates->count() !== 1) {
                        return $carry;
                    }

                    return $carry->with($pincer, $pincerCandidates);
                },
                Map::empty(),
            );
    }

    /**
     * @param Map<FillableCell, Candidates> $candidatesByPotentialFirstPincers
     * @param Map<FillableCell, Candidates> $candidatesByPotentialSecondPincers
     *
     * @return iterable<array{FillableCell, FillableCell, Value}>
     */
    private function associatePincers(
        Map $candidatesByPotentialFirstPincers,
        Map $candidatesByPotentialSecondPincers,
        Candidates $pivotCandidates,
    ): iterable {
        foreach ($candidatesByPotentialFirstPincers as $firstPincer => $firstPincerCandidates) {
            foreach ($candidatesByPotentialSecondPincers as $secondPincer => $secondPincerCandidates) {
                if ($firstPincer->hasCommonGroupWith($secondPincer)) {
                    continue;
                }

                $pincersCommonCandidates = $firstPincerCandidates->intersect($secondPincerCandidates);
                if ($pincersCommonCandidates->count() !== 1) {
                    continue;
                }

                $intersectAllCandidates = $pivotCandidates->intersect($firstPincerCandidates, $secondPincerCandidates);
                if ($intersectAllCandidates->count() !== 0) {
                    continue;
                }

                yield [$firstPincer, $secondPincer, $pincersCommonCandidates->first()];
            }
        }
    }
}
