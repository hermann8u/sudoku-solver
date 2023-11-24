<?php

declare(strict_types=1);

namespace SudokuSolver\Solver\Association\Extractor;

use SudokuSolver\Solver\Association\AssociationExtractor;
use SudokuSolver\Solver\Association\Pair;
use SudokuSolver\Solver\Candidates;
use SudokuSolver\Solver\CellCandidatesMap;

/**
 * @implements AssociationExtractor<Pair>
 */
final readonly class PairExtractor implements AssociationExtractor
{
    public function getAssociationsForGroup(CellCandidatesMap $mapForGroup): array
    {
        $mapForGroup = $mapForGroup->filter(static fn (Candidates $c) => $c->count() === Pair::COUNT);

        foreach ($mapForGroup as $cell => $candidates) {
            $cellsByCandidates[$candidates->toString()][] = $cell;
        }

        foreach ($cellsByCandidates ?? [] as $candidatesString => $cells) {
            if (count($cells) !== Pair::COUNT) {
                continue;
            }

            $pairs[] = new Pair($cells, Candidates::fromString($candidatesString));
        }

        return $pairs ?? [];
    }

    public static function getAssociationType(): string
    {
        return Pair::class;
    }
}
