<?php

declare(strict_types=1);

namespace SudokuSolver\Solver\Association\Extractor;

use SudokuSolver\Solver\Association\Triplet;
use SudokuSolver\Solver\Candidates;
use SudokuSolver\Solver\CellCandidatesMap;
use SudokuSolver\Solver\Association\AssociationExtractor;

/**
 * @implements AssociationExtractor<Triplet>
 */
final readonly class TripletExtractor implements AssociationExtractor
{
    public function getAssociationsForGroup(CellCandidatesMap $mapForGroup): array
    {
        $mapForGroup = $mapForGroup->filter(static fn (Candidates $c) => $c->count() <= Triplet::COUNT);

        $coordinatesByCandidates = $mapForGroup->multidimensionalKeyLoop($this->tryToAssociateCells(...));

        foreach ($coordinatesByCandidates as $valuesString => $coordinatesTriplet) {
            if (count($coordinatesTriplet) !== Triplet::COUNT) {
                continue;
            }

            $triplets[] = Triplet::fromStrings($coordinatesTriplet, $valuesString);
        }

        return $triplets ?? [];
    }

    public static function getAssociationType(): string
    {
        return Triplet::class;
    }

    /**
     * @param array<string, string[]> $carry
     *
     * @return array<string, string[]>
     */
    private function tryToAssociateCells(
        CellCandidatesMap $mapForGroup,
        array $carry,
        string $a,
        string $b,
    ): array {
        [$candidatesWithSmallerCount, $candidatesWithBiggerCount] = $this->sortByCount(
            $mapForGroup->get($a),
            $mapForGroup->get($b),
        );

        if ($candidatesWithBiggerCount->count() < Triplet::COUNT) {
            return $carry;
        }

        if (! $candidatesWithBiggerCount->hasAll($candidatesWithSmallerCount)) {
            return $carry;
        }

        $key = $candidatesWithBiggerCount->toString();

        $carry[$key][] = $a;
        $carry[$key][] = $b;

        $carry[$key] = array_unique($carry[$key]);

        return $carry;
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
}
