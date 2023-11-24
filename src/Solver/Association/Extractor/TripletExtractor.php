<?php

declare(strict_types=1);

namespace SudokuSolver\Solver\Association\Extractor;

use SudokuSolver\Grid\Cell\FillableCell;
use SudokuSolver\Solver\Association\AssociationExtractor;
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
        $mapForGroup = $mapForGroup->filter(static fn (Candidates $c) => $c->count() <= Triplet::COUNT);

        $cellsByCandidates = $mapForGroup->multidimensionalLoop($this->tryToAssociateCells(...));

        foreach ($cellsByCandidates as $candidatesString => $cells) {
            if (count($cells) !== Triplet::COUNT) {
                continue;
            }

            $triplets[] = new Triplet(array_values($cells), Candidates::fromString($candidatesString));
        }

        return $triplets ?? [];
    }

    public static function getAssociationType(): string
    {
        return Triplet::class;
    }

    /**
     * @param array<string, FillableCell[]> $carry
     *
     * @return array<string, FillableCell[]>
     */
    private function tryToAssociateCells(
        CellCandidatesMap $mapForGroup,
        array $carry,
        FillableCell $a,
        FillableCell $b,
    ): array {
        [$candidatesWithSmallerCount, $candidatesWithBiggerCount] = $this->sortByCount(
            $mapForGroup->get($a),
            $mapForGroup->get($b),
        );

        if ($candidatesWithBiggerCount->count() < Triplet::COUNT) {
            return $carry;
        }

        if (! $candidatesWithBiggerCount->contains($candidatesWithSmallerCount)) {
            return $carry;
        }

        $key = $candidatesWithBiggerCount->toString();

        $carry[$key][$a->coordinates->toString()] = $a;
        $carry[$key][$b->coordinates->toString()] = $b;

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
