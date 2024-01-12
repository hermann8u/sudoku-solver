<?php

declare(strict_types=1);

namespace SudokuSolver\Solver\Association\Extractor;

use SudokuSolver\DataStructure\ArrayList;
use SudokuSolver\DataStructure\Map;
use SudokuSolver\Grid\Cell\FillableCell;
use SudokuSolver\Solver\Association\AssociationExtractor;
use SudokuSolver\Solver\Association\Pair;
use SudokuSolver\Solver\Association\Triplet;
use SudokuSolver\Solver\Candidates;
use SudokuSolver\Solver\CellCandidatesMap;

/**
 * @implements AssociationExtractor<Triplet>
 */
final readonly class TripletExtractor implements AssociationExtractor
{
    public function getAssociationsForGroup(CellCandidatesMap $mapForGroup): array
    {
        /** @var Map<Candidates, ArrayList<FillableCell>> $cellsByCandidates */
        $cellsByCandidates = Map::empty();

        $cellsByCandidates = $mapForGroup
            ->filter(static fn (Candidates $c) => \in_array($c->count(), [Pair::COUNT, Triplet::COUNT], true))
            ->multidimensionalLoop($this->tryToAssociateCells(...), $cellsByCandidates);

        foreach ($cellsByCandidates as $candidates => $cells) {
            if ($cells->count() !== Triplet::COUNT) {
                continue;
            }

            $triplets[] = new Triplet($cells, $candidates);
        }

        return $triplets ?? [];
    }

    public static function getAssociationType(): string
    {
        return Triplet::class;
    }

    /**
     * @param Map<Candidates, ArrayList<FillableCell>> $carry
     *
     * @return Map<Candidates, ArrayList<FillableCell>>
     */
    private function tryToAssociateCells(
        CellCandidatesMap $mapForGroup,
        Map $carry,
        FillableCell $a,
        FillableCell $b,
    ): Map {
        $candidatesA = $mapForGroup->get($a);
        $candidatesB = $mapForGroup->get($b);

        [$candidatesWithSmallerCount, $candidatesWithBiggerCount] = $this->sortByCount(
            $candidatesA,
            $candidatesB,
        );

        $candidates = match ($candidatesWithBiggerCount->count()) {
            Pair::COUNT => $this->getCandidatesForHiddenTriplet($candidatesWithBiggerCount, $candidatesWithSmallerCount),
            Triplet::COUNT => $this->getCandidatesForOtherTriplet($candidatesWithBiggerCount, $candidatesWithSmallerCount),
            default => throw new \LogicException(),
        };

        if ($candidates === null) {
            return $carry;
        }

        try {
            $cells = $carry->get($candidates);
        } catch (\OutOfBoundsException) {
            /** @var ArrayList<FillableCell> $cells */
            $cells = ArrayList::empty();
        }

        if (! $cells->contains($a)) {
            $cells = $cells->merge($a);
        }

        if (! $cells->contains($b)) {
            $cells = $cells->merge($b);
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
        if ($candidatesA->intersect($candidatesB)->count() !== 1) {
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
