<?php

declare(strict_types=1);

namespace Sudoku\Solver\Association\Extractor;

use Sudoku\DataStructure\ArrayList;
use Sudoku\DataStructure\Map;
use Sudoku\Grid\Cell\FillableCell;
use Sudoku\Grid\Group;
use Sudoku\Solver\Association\AssociationExtractor;
use Sudoku\Solver\Association\Pair;
use Sudoku\Solver\Association\Triplet;
use Sudoku\Solver\Candidates;

/**
 * @implements AssociationExtractor<Triplet>
 */
final readonly class TripletExtractor implements AssociationExtractor
{
    /**
     * @inheritdoc
     */
    public function getAssociationsInGroup(Map $candidatesByCell, Group $group): iterable
    {
        $groupCells = $group->getEmptyCells();

        /** @var Map<Candidates, ArrayList<FillableCell>> $cellsByCandidates */
        $cellsByCandidates = $groupCells->multidimensionalLoop(
            fn (Map $carry, FillableCell $a, FillableCell $b) => $this->tryToAssociateCells($candidatesByCell, $carry, $a, $b),
            Map::empty(),
        );

        foreach ($cellsByCandidates as $candidates => $cells) {
            if ($cells->count() !== Triplet::COUNT) {
                continue;
            }

            yield new Triplet($group, $candidates, $cells);
        }
    }

    public static function getAssociationType(): string
    {
        return Triplet::class;
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
        FillableCell $a,
        FillableCell $b,
    ): Map {
        $candidatesA = $map->get($a);
        $candidatesB = $map->get($b);

        [$candidatesWithSmallerCount, $candidatesWithBiggerCount] = $this->sortByCount(
            $candidatesA,
            $candidatesB,
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
        } catch (\OutOfBoundsException) {
            $cells = ArrayList::empty();
        }

        if (! $cells->contains($a)) {
            $cells = $cells->with($a);
        }

        if (! $cells->contains($b)) {
            $cells = $cells->with($b);
        }

        return $carry->with($candidates, $cells);
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
