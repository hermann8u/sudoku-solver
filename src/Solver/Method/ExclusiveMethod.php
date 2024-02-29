<?php

declare(strict_types=1);

namespace Sudoku\Solver\Method;

use Sudoku\DataStructure\Map;
use Sudoku\Grid;
use Sudoku\Grid\Cell\FillableCell;
use Sudoku\Grid\Group;
use Sudoku\Solver\Candidates;
use Sudoku\Solver\Method;

final readonly class ExclusiveMethod implements Method
{
    public static function getName(): string
    {
        return 'exclusive';
    }

    /**
     * @inheritdoc
     */
    public function apply(Map $candidatesByCell, Grid $grid, FillableCell $currentCell): Map
    {
        $initialCandidates = $candidatesByCell->get($currentCell);

        /** @var Group $group */
        foreach ($grid->getGroupsForCell($currentCell) as $group) {
            $candidates = $initialCandidates;

            $candidates = $group->getEmptyCells()
                ->filter($currentCell->isNot(...))
                ->reduce(
                    static fn (Candidates $carry, FillableCell $cell) =>
                        $carry->withRemovedValues(...$candidatesByCell->get($cell)->values),
                    $candidates,
                );

            if ($candidates->count() === 1) {
                return $candidatesByCell->with($currentCell, $candidates);
            }
        }

        return $candidatesByCell;
    }
}
