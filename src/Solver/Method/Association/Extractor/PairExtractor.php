<?php

declare(strict_types=1);

namespace Sudoku\Solver\Method\Association\Extractor;

use Sudoku\DataStructure\ArrayList;
use Sudoku\DataStructure\Map;
use Sudoku\Grid;
use Sudoku\Grid\Cell\FillableCell;
use Sudoku\Grid\Group;
use Sudoku\Solver\Association\Naked\Pair;
use Sudoku\Solver\Method\Association\AssociationExtractor;

/**
 * @implements AssociationExtractor<Pair>
 */
final readonly class PairExtractor implements AssociationExtractor
{
    public static function getAssociationType(): string
    {
        return Pair::class;
    }

    /**
     * @inheritdoc
     */
    public function getAssociationWithCell(Map $candidatesByCell, Grid $grid, FillableCell $currentCell): iterable
    {
        $currentCellCandidates = $candidatesByCell->get($currentCell);

        if ($currentCellCandidates->count() !== Pair::COUNT) {
            return;
        }

        $groups = $grid->getGroupsForCell($currentCell);

        /** @var Group $group */
        foreach ($groups as $group) {
            $cells = $group->getEmptyCells()->filter($currentCell->isNot(...));

            // We don't need to identify association in groups with less remaining cells than association count
            if ($cells->count() < Pair::COUNT) {
                continue;
            }

            foreach ($cells as $relatedCell) {
                $relatedCellCandidates = $candidatesByCell->get($relatedCell);

                if ($relatedCellCandidates->count() !== Pair::COUNT) {
                    continue;
                }

                $candidates = $currentCellCandidates->intersect($relatedCellCandidates);

                if ($candidates->count() !== Pair::COUNT) {
                    continue;
                }

                yield new Pair(
                    $group,
                    ArrayList::fromItems($currentCell, $relatedCell),
                    $candidates->values,
                );
            }
        }
    }
}
