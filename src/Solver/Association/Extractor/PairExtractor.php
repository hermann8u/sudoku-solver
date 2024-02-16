<?php

declare(strict_types=1);

namespace Sudoku\Solver\Association\Extractor;

use OutOfBoundsException;
use Sudoku\DataStructure\ArrayList;
use Sudoku\DataStructure\Map;
use Sudoku\Grid\Cell\FillableCell;
use Sudoku\Grid\Group;
use Sudoku\Solver\Association\AssociationExtractor;
use Sudoku\Solver\Association\Pair;
use Sudoku\Solver\Candidates;

/**
 * @implements AssociationExtractor<Pair>
 */
final readonly class PairExtractor implements AssociationExtractor
{
    /**
     * @inheritdoc
     */
    public function getAssociationsInGroup(Map $candidatesByCell, Group $group): iterable
    {
        /** @var Map<Candidates, ArrayList<FillableCell>> $cellsByCandidates */
        $cellsByCandidates = $group->getEmptyCells()->reduce(
            function (Map $carry, FillableCell $cell) use ($candidatesByCell) {
                $candidates = $candidatesByCell->get($cell);

                if ($candidates->count() !== 2) {
                    return $carry;
                }

                try {
                    /** @var ArrayList<FillableCell> $cells */
                    $cells = $carry->get($candidates);
                } catch (OutOfBoundsException) {
                    $cells = ArrayList::empty();
                }

                return $carry->with($candidates, $cells->with($cell));
            },
            Map::empty(),
        );

        foreach ($cellsByCandidates as $candidates => $cells) {
            if ($cells->count() !== Pair::COUNT) {
                continue;
            }

            yield new Pair($group, $cells, $candidates->values);
        }
    }

    public static function getAssociationType(): string
    {
        return Pair::class;
    }
}
