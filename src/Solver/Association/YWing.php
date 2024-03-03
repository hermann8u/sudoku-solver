<?php

declare(strict_types=1);

namespace Sudoku\Solver\Association;

use Sudoku\DataStructure\ArrayList;
use Sudoku\Grid;
use Sudoku\Grid\Cell\FillableCell;
use Sudoku\Grid\Cell\Value;
use Sudoku\Grid\Group;
use Sudoku\Solver\Association;
use Webmozart\Assert\Assert;

final class YWing implements Association
{
    /**
     * @param ArrayList<FillableCell> $pincers
     */
    public function __construct(
        public FillableCell $pivot,
        public ArrayList $pincers,
        public Value $value,
    ) {
        Assert::count($this->pincers, 2);
    }

    public function getTargetedCells(Grid $grid): ArrayList
    {
        $associationCells = $this->pincers->with($this->pivot);

        /** @var ArrayList<ArrayList<FillableCell>> $emptyCellsTargetedByPincers */
        $emptyCellsTargetedByPincers = $this->pincers
            ->map(static fn (FillableCell $pincer) => $grid->getGroupsForCell($pincer))
            ->reduce(
                fn (ArrayList $carry, ArrayList $groupsForPincer) => $carry->with(
                    $this->getEmptyCellsInGroups($groupsForPincer)
                        ->filter(fn (FillableCell $c) => ! $associationCells->exists($c->is(...)))
                        ->unique(static fn (FillableCell $a, FillableCell $b) => $a->is($b)),
                ),
                ArrayList::empty(),
            );

        return $emptyCellsTargetedByPincers->multidimensionalLoop($this->getCommonCells(...), ArrayList::empty());
    }

    public function getCandidatesToEliminate(): ArrayList
    {
        return ArrayList::fromItems($this->value);
    }

    public function toString(): string
    {
        return sprintf(
            'Y Wing : %s => %s => %s',
            $this->pivot->coordinates->toString(),
            $this->pincers
                ->map(static fn (FillableCell $c) => $c->coordinates->toString())
                ->implode(' '),
            $this->value,
        );
    }

    public function __toString(): string
    {
        return $this->toString();
    }

    /**
     * @param ArrayList<Group> $groups
     *
     * @return ArrayList<FillableCell>
     */
    private function getEmptyCellsInGroups(ArrayList $groups): ArrayList
    {
        return $groups
            ->map(static fn (Group $group) => $group->getEmptyCells())
            ->reduce(
                static fn (ArrayList $carry, ArrayList $emptyCells) => $carry->with(...$emptyCells),
                ArrayList::empty(),
            );
    }

    /**
     * @param ArrayList<FillableCell> $carry
     * @param ArrayList<FillableCell> $firstCells
     * @param ArrayList<FillableCell> $secondCells
     *
     * @return ArrayList<FillableCell>
     */
    private function getCommonCells(ArrayList $carry, ArrayList $firstCells, ArrayList $secondCells): ArrayList
    {
        $commonCells = $firstCells->filter(static fn (FillableCell $c) => $secondCells->exists($c->is(...)));

        return $carry->merge($commonCells);
    }
}
