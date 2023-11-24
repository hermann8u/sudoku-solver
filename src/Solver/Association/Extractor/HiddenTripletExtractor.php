<?php

declare(strict_types=1);

namespace SudokuSolver\Solver\Association\Extractor;

use SudokuSolver\Grid\Cell\FillableCell;
use SudokuSolver\Solver\Association\AssociationExtractor;
use SudokuSolver\Solver\Association\Pair;
use SudokuSolver\Solver\Association\Triplet;
use SudokuSolver\Solver\Candidates;
use SudokuSolver\Solver\CellCandidatesMap;

/**
 * @implements AssociationExtractor<Triplet>
 */
final readonly class HiddenTripletExtractor implements AssociationExtractor
{
    public function getAssociationsForGroup(CellCandidatesMap $mapForGroup): array
    {
        $mapForGroup = $mapForGroup->filter(static fn (Candidates $c) => $c->count() === Pair::COUNT);

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
        $candidatesA = $mapForGroup->get($a);
        $candidatesB = $mapForGroup->get($b);

        if ($candidatesA->intersect($candidatesB)->count() !== 1) {
            return $carry;
        }

        $candidates = $candidatesA->merge($candidatesB);

        $key = $candidates->toString();

        $carry[$key][$a->coordinates->toString()] = $a;
        $carry[$key][$b->coordinates->toString()] = $b;

        return $carry;
    }
}
