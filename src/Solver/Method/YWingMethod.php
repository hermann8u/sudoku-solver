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
use Sudoku\Solver\YWing;

final class YWingMethod implements Method
{
    public static function getName(): string
    {
        return 'y_wing';
    }

    /**
     * @inheritDoc
     */
    public function apply(Map $candidatesByCell, Grid $grid, FillableCell $currentCell): Map
    {
        $yWings = $this->buildYWings($candidatesByCell, $grid, $currentCell);

        /** @var YWing $yWing */
        foreach ($yWings as $yWing) {
            foreach ($yWing->getTargetedFillableCells($grid) as $cell) {
                $candidatesByCell = $candidatesByCell->with(
                    $cell,
                    $candidatesByCell->get($cell)->withRemovedValues($yWing->value),
                );
            }
        }

        return $candidatesByCell;
    }

    /**
     * @param Map<FillableCell, Candidates> $candidatesByCell
     *
     * @return ArrayList<YWing>
     */
    private function buildYWings(Map $candidatesByCell, Grid $grid, FillableCell $pivot): ArrayList
    {
        $pivotCandidates = $candidatesByCell->get($pivot);

        if ($pivotCandidates->count() !== 2) {
            return ArrayList::empty();
        }

        return $grid->getGroupsForCell($pivot)
            ->map(fn (Group $group) => $this->getCandidatesByPotentialPincerInGroup(
                $candidatesByCell,
                $group,
                $pivot,
            ))
            ->multidimensionalLoop(
                function (
                    ArrayList $carry,
                    Map $candidatesByPotentialFirstPincers,
                    Map $candidatesByPotentialSecondPincers,
                ) use ($pivot, $pivotCandidates) {
                    $pincerAssociations = $this->associatePincers(
                        $candidatesByPotentialFirstPincers,
                        $candidatesByPotentialSecondPincers,
                        $pivotCandidates,
                    );

                    foreach ($pincerAssociations as [$firstPincer, $secondPincer, $value]) {
                        $carry = $carry->with(new YWing(
                            $pivot,
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
            ->filter(static fn (FillableCell $c) => ! $c->is($pivot))
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
