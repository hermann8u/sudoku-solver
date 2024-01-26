<?php

declare(strict_types=1);

namespace Sudoku\Solver\Method;

use Sudoku\DataStructure\ArrayList;
use Sudoku\DataStructure\Map;
use Sudoku\Grid;
use Sudoku\Grid\Cell\FillableCell;
use Sudoku\Grid\Cell\Value;
use Sudoku\Grid\Group;
use Sudoku\Solver\Candidates;
use Sudoku\Solver\CandidatesProvider;
use Sudoku\Solver\Method;

final readonly class InclusiveMethod implements Method, CandidatesProvider
{
    public static function getName(): string
    {
        return 'inclusive';
    }

    /**
     * @inheritdoc
     */
    public function apply(Map $candidatesByCell, Grid $grid, FillableCell $currentCell): Map
    {
        return $candidatesByCell->with($currentCell, $this->getCandidates($grid, $currentCell));
    }

    public function getCandidates(Grid $grid, FillableCell $cell): Candidates
    {
        /** @var ArrayList<Value> $presentValuesInCellGroups */
        $presentValuesInCellGroups = $grid->getGroupsForCell($cell)->reduce(
            static fn (ArrayList $carry, Group $group) => $carry->with(...$group->getPresentValues()),
            ArrayList::empty(),
        );

        return Candidates::all()->withRemovedValues(...$presentValuesInCellGroups);
    }
}
