<?php

declare(strict_types=1);

namespace SudokuSolver\Solver\Association\Extractor;

use SudokuSolver\Solver\Association\AssociationExtractor;
use SudokuSolver\Solver\Association\Triplet;
use SudokuSolver\Solver\Candidates;
use SudokuSolver\Solver\CellCandidatesMap;

/**
 * @implements AssociationExtractor<Triplet>
 */
final class HiddenTripletExtractor implements AssociationExtractor
{
    public function getAssociationsForGroup(CellCandidatesMap $mapForGroup): array
    {
        $mapForGroup = $mapForGroup->filtered(static fn (Candidates $c) => $c->count() < Triplet::COUNT);

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
        $candidatesA = $mapForGroup->get($a);
        $candidatesB = $mapForGroup->get($b);

        if ($candidatesA->intersect($candidatesB)->count() !== 1) {
            return $carry;
        }

        $candidates = $candidatesA->merge($candidatesB);

        $key = $candidates->toString();

        $carry[$key][] = $a;
        $carry[$key][] = $b;

        $carry[$key] = array_unique($carry[$key]);

        return $carry;
    }
}
