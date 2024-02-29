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
        if ($candidatesByCell->has($currentCell)) {
            return $candidatesByCell;
        }

        return $candidatesByCell->with($currentCell, $this->getCandidates($grid, $currentCell));
    }

    public function getCandidates(Grid $grid, FillableCell $cell): Candidates
    {
        /** @var ArrayList<Value> $presentValuesInCellGroups */
        $presentValuesInCellGroups = $grid->getGroupsForCell($cell)->reduce(
            $this->mergeValuesInGroup(...),
            ArrayList::empty(),
        );

        return Candidates::fromAllValues()->withRemovedValues(...$presentValuesInCellGroups);
    }

    /**
     * @param ArrayList<Value> $carry
     *
     * @return ArrayList<Value>
     */
    private function mergeValuesInGroup(ArrayList $carry, Group $group): ArrayList
    {
        return $carry->merge($group->getPresentValues());
    }
}
