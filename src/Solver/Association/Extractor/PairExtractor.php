<?php

declare(strict_types=1);

namespace Florian\SudokuSolver\Solver\Association\Extractor;

use Florian\SudokuSolver\Solver\Association\Pair;
use Florian\SudokuSolver\Solver\Candidates;
use Florian\SudokuSolver\Solver\CellCandidatesMap;
use Florian\SudokuSolver\Solver\Association\AssociationExtractor;

/**
 * @implements AssociationExtractor<Pair>
 */
final class PairExtractor implements AssociationExtractor
{
    public function getAssociationsForGroup(CellCandidatesMap $mapForGroup): array
    {
        $mapForGroup = $mapForGroup->filtered(static fn (Candidates $c) => $c->count() === Pair::COUNT);

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
