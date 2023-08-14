<?php

declare(strict_types=1);

namespace SudokuSolver\Solver\Association\Extractor;

use SudokuSolver\Solver\Association\Pair;
use SudokuSolver\Solver\Candidates;
use SudokuSolver\Solver\CellCandidatesMap;
use SudokuSolver\Solver\Association\AssociationExtractor;

/**
 * @implements AssociationExtractor<Pair>
 */
final readonly class PairExtractor implements AssociationExtractor
{
    public function getAssociationsForGroup(CellCandidatesMap $mapForGroup): array
    {
        $mapForGroup = $mapForGroup->filter(static fn (Candidates $c) => $c->count() === Pair::COUNT);

        foreach ($mapForGroup as $coordinates => $candidates) {
            $coordinatesByCandidates[$candidates->toString()][] = $coordinates;
        }

        foreach ($coordinatesByCandidates ?? [] as $valuesString => $coordinatesPair) {
            if (count($coordinatesPair) !== Pair::COUNT) {
                continue;
            }

            $pairs[] = Pair::fromStrings($coordinatesPair, $valuesString);
        }

        return $pairs ?? [];
    }

    public static function getAssociationType(): string
    {
        return Pair::class;
    }
}
