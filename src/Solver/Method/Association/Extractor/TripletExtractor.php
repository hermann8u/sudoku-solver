<?php

declare(strict_types=1);

namespace Sudoku\Solver\Method\Association\Extractor;

use OutOfBoundsException;
use Sudoku\DataStructure\ArrayList;
use Sudoku\DataStructure\Map;
use Sudoku\Grid;
use Sudoku\Grid\Cell\FillableCell;
use Sudoku\Grid\Group;
use Sudoku\Solver\Association\Naked\Pair;
use Sudoku\Solver\Association\Naked\Triplet;
use Sudoku\Solver\Candidates;
use Sudoku\Solver\Method\Association\AssociationExtractor;

/**
 * @implements AssociationExtractor<Triplet>
 */
final readonly class TripletExtractor implements AssociationExtractor
{
    public static function getAssociationType(): string
    {
        return Triplet::class;
    }

    /**
     * @inheritDoc
     */
    public function getAssociationWithCell(Map $candidatesByCell, Grid $grid, FillableCell $currentCell): iterable
    {
        if ($candidatesByCell->get($currentCell)->count() > Triplet::COUNT) {
            return;
        }

        $groups = $grid->getGroupsForCell($currentCell);

        /** @var Group $group */
        foreach ($groups as $group) {
            $cells = $group->getEmptyCells()->filter($currentCell->isNot(...));

            // We don't need to identify association in groups with less remaining cells than association count
            if ($cells->count() < Triplet::COUNT) {
                continue;
            }

            /** @var Map<Candidates, ArrayList<FillableCell>> $cellsByCandidates */
            $cellsByCandidates = $cells->reduce(
                fn (Map $carry, FillableCell $relatedCell) => $this->tryToAssociateCells(
                    $candidatesByCell,
                    $carry,
                    $currentCell,
                    $relatedCell,
                ),
                Map::empty(),
            );

            foreach ($cellsByCandidates as $candidates => $cells) {
                if ($cells->count() !== Triplet::COUNT) {
                    continue;
                }

                yield new Triplet($group, $cells, $candidates->values);
            }
        }
    }

    /**
     * @param Map<FillableCell, Candidates> $map
     * @param Map<Candidates, ArrayList<FillableCell>> $carry
     *
     * @return Map<Candidates, ArrayList<FillableCell>>
     */
    private function tryToAssociateCells(
        Map $map,
        Map $carry,
        FillableCell $currentCell,
        FillableCell $relatedCell,
    ): Map {
        $currentCellCandidates = $map->get($currentCell);
        $relatedCellCandidates = $map->get($relatedCell);

        [$candidatesWithSmallerCount, $candidatesWithBiggerCount] = $this->sortByCount(
            $currentCellCandidates,
            $relatedCellCandidates,
        );

        $candidates = match ($candidatesWithBiggerCount->count()) {
            Pair::COUNT => $this->getCandidatesForHiddenTriplet($candidatesWithBiggerCount, $candidatesWithSmallerCount),
            Triplet::COUNT => $this->getCandidatesForOtherTriplet($candidatesWithBiggerCount, $candidatesWithSmallerCount),
            default => null,
        };

        if ($candidates === null) {
            return $carry;
        }

        try {
            $cells = $carry->get($candidates);
        } catch (OutOfBoundsException) {
            return $carry->with($candidates, ArrayList::fromItems($currentCell, $relatedCell));
        }

        return $carry->with($candidates, $cells->with($relatedCell));
    }

    /**
     * @param Candidates $candidates
     * @param Candidates $otherCandidates
     *
     * @return array{Candidates, Candidates}
     */
    private function sortByCount(Candidates $candidates, Candidates $otherCandidates): array
    {
        $v = [$candidates, $otherCandidates];
        usort($v, static fn (Candidates $a, Candidates $b) => $a->count() <=> $b->count());

        return $v;
    }

    private function getCandidatesForHiddenTriplet(Candidates $candidatesA, Candidates $candidatesB): ?Candidates
    {
        if ($candidatesA->intersect($candidatesB)->count() === 0) {
            return null;
        }

        return $candidatesA->merge($candidatesB);
    }

    private function getCandidatesForOtherTriplet(Candidates $candidatesWithBiggerCount, Candidates $candidatesWithSmallerCount): ?Candidates
    {
        if (! $candidatesWithBiggerCount->contains($candidatesWithSmallerCount)) {
            return null;
        }

        return $candidatesWithBiggerCount;
    }
}
