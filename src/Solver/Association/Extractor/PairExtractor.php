<?php

declare(strict_types=1);

namespace SudokuSolver\Solver\Association\Extractor;

use SudokuSolver\DataStructure\Map;
use SudokuSolver\Grid\Cell\FillableCell;
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

        /** @var Map<Candidates, FillableCell[]> $cellsByCandidates */
        $cellsByCandidates = Map::empty();

        foreach ($mapForGroup as $cell => $candidates) {
            try {
                $cells = $cellsByCandidates->get($candidates);
            } catch (\OutOfBoundsException) {
                $cells = [];
            }

            $cells[] = $cell;

            $cellsByCandidates = $cellsByCandidates->with($candidates, $cells);
        }

        foreach ($cellsByCandidates as $candidates => $cells) {
            if (\count($cells) !== Pair::COUNT) {
                continue;
            }

            $pairs[] = new Pair($cells, $candidates);
        }

        return $pairs ?? [];
    }

    public static function getAssociationType(): string
    {
        return Pair::class;
    }
}
