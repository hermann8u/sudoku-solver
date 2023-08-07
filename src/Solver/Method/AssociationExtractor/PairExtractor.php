<?php

declare(strict_types=1);

namespace Florian\SudokuSolver\Solver\Method\AssociationExtractor;

use Florian\SudokuSolver\Solver\Association\Pair;
use Florian\SudokuSolver\Solver\Candidates;
use Florian\SudokuSolver\Solver\CellCandidatesMap;
use Florian\SudokuSolver\Solver\Method\AssociationExtractor;

/**
 * @implements AssociationExtractor<Pair>
 */
final class PairExtractor implements AssociationExtractor
{
    public function getAssociationsForGroup(CellCandidatesMap $mapForGroup): array
    {
        $mapForGroup = $mapForGroup->filtered(static fn (Candidates $c) => $c->count() === 2);

        foreach ($mapForGroup as $coordinates => $candidates) {
            $coordinatesByCandidates[$candidates->toString()][] = $coordinates;
        }

        foreach ($coordinatesByCandidates ?? [] as $valuesString => $coordinatesPair) {
            if (count($coordinatesPair) !== 2) {
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
