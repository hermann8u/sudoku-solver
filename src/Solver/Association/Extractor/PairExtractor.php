<?php

declare(strict_types=1);

namespace SudokuSolver\Solver\Association\Extractor;

use SudokuSolver\DataStructure\ArrayList;
use SudokuSolver\DataStructure\Map;
use SudokuSolver\Grid\Cell\FillableCell;
use SudokuSolver\Grid\Group;
use SudokuSolver\Solver\Association\AssociationExtractor;
use SudokuSolver\Solver\Association\Pair;
use SudokuSolver\Solver\Candidates;
use SudokuSolver\Solver\CellCandidatesMap;

/**
 * @implements AssociationExtractor<Pair>
 */
final readonly class PairExtractor implements AssociationExtractor
{
    public function getAssociationsInGroup(CellCandidatesMap $map, Group $group): ArrayList
    {
        $groupCells = $group->getEmptyCells();

        /** @var Map<Candidates, ArrayList<FillableCell>> $cellsByCandidates */
        $cellsByCandidates = $groupCells->reduce(function (Map $carry, FillableCell $cell) use ($map) {
            $candidates = $map->get($cell);

            if ($candidates->count() !== 2) {
                return $carry;
            }

            try {
                /** @var ArrayList<FillableCell> $cells */
                $cells = $carry->get($candidates);
            } catch (\OutOfBoundsException) {
                $cells = ArrayList::empty();
            }

            return $carry->with($candidates, $cells->with($cell));
        }, Map::empty());

        $pairs = ArrayList::empty();

        foreach ($cellsByCandidates as $candidates => $cells) {
            if ($cells->count() !== Pair::COUNT) {
                continue;
            }

            $pairs = $pairs->with(new Pair($group, $candidates, $cells));
        }

        return $pairs;
    }

    public static function getAssociationType(): string
    {
        return Pair::class;
    }
}
