<?php

declare(strict_types=1);

namespace SudokuSolver\Solver\Method;

use SudokuSolver\DataStructure\ArrayList;
use SudokuSolver\Grid\Cell\FillableCell;
use SudokuSolver\Grid\Cell\Value;
use SudokuSolver\Grid\Grid;
use SudokuSolver\Grid\Group;
use SudokuSolver\Solver\Candidates;
use SudokuSolver\Solver\CandidatesProvider;
use SudokuSolver\Solver\CellCandidatesMap;
use SudokuSolver\Solver\Method;

final readonly class InclusiveMethod implements Method, CandidatesProvider
{
    public static function getName(): string
    {
        return 'inclusive';
    }

    public function apply(CellCandidatesMap $map, Grid $grid, FillableCell $currentCell): CellCandidatesMap
    {
        return $map->with($currentCell, $this->getCandidates($grid, $currentCell));
    }

    public function getCandidates(Grid $grid, FillableCell $cell): Candidates
    {
        /** @var ArrayList<Value> $presentValuesInCellGroups */
        $presentValuesInCellGroups = $grid->getGroupsForCell($cell)->reduce(
            static fn (ArrayList $carry, Group $group) => $carry->merge(...$group->getPresentValues()),
            ArrayList::empty(),
        );

        return Candidates::all()->withRemovedValues(...$presentValuesInCellGroups);
    }
}
