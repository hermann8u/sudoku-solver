<?php

declare(strict_types=1);

namespace SudokuSolver\Solver\Association\Extractor;

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
final readonly class HiddenTripletExtractor implements AssociationExtractor
{
    public function getAssociationsForGroup(CellCandidatesMap $mapForGroup): array
    {
        $mapForGroup = $mapForGroup->filter(static fn (Candidates $c) => $c->count() === Pair::COUNT);

        /** @var Map<Candidates, array<string, FillableCell>> $cellsByCandidates */
        $cellsByCandidates = Map::empty();
        $cellsByCandidates = $mapForGroup->multidimensionalLoop($this->tryToAssociateCells(...), $cellsByCandidates);

        foreach ($cellsByCandidates as $candidates => $cells) {
            if (\count($cells) !== Triplet::COUNT) {
                continue;
            }

            $triplets[] = new Triplet(array_values($cells), $candidates);
        }

        return $triplets ?? [];
    }

    public static function getAssociationType(): string
    {
        return Triplet::class;
    }

    /**
     * @param Map<Candidates, array<string, FillableCell>> $carry
     *
     * @return Map<Candidates, array<string, FillableCell>>
     */
    private function tryToAssociateCells(
        CellCandidatesMap $mapForGroup,
        Map $carry,
        FillableCell $a,
        FillableCell $b,
    ): Map {
        $candidatesA = $mapForGroup->get($a);
        $candidatesB = $mapForGroup->get($b);

        if ($candidatesA->intersect($candidatesB)->count() !== 1) {
            return $carry;
        }

        $candidates = $candidatesA->merge($candidatesB);

        try {
            $cells = $carry->get($candidates);
        } catch (\OutOfBoundsException) {
            $cells = [];
        }

        $cells[$a->coordinates->toString()] = $a;
        $cells[$b->coordinates->toString()] = $b;

        return $carry->with($candidates, $cells);
    }
}
