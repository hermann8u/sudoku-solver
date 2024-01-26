<?php

declare(strict_types=1);

namespace Sudoku\Solver\Association\Extractor;

use Sudoku\DataStructure\ArrayList;
use Sudoku\DataStructure\Map;
use Sudoku\Grid\Cell\FillableCell;
use Sudoku\Grid\Group;
use Sudoku\Solver\Association\AssociationExtractor;
use Sudoku\Solver\Association\Pair;
use Sudoku\Solver\Candidates;
use Sudoku\Solver\CellCandidatesMap;

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
