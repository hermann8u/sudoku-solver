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

abstract readonly class NakedAssociation implements Association
{
    /**
     * @param ArrayList<FillableCell> $cells
     * @param ArrayList<Value> $values
     */
    public function __construct(
        public Group $group,
        public ArrayList $cells,
        public ArrayList $values,
    ) {
        Assert::count($this->cells, $this->getAssociationCount());
        Assert::count($this->values, $this->getAssociationCount());
    }

    public function getTargetedCells(Grid $grid): ArrayList
    {
        return $this->group->getEmptyCells()->filter(fn (FillableCell $c) => ! $this->cells->exists($c->is(...)));
    }

    public function getCandidatesToEliminate(): ArrayList
    {
        return $this->values;
    }

    public function toString(): string
    {
        return sprintf(
            '%s => %s',
            $this->values->implode(','),
            $this->cells
                ->map(static fn (FillableCell $c) => $c->coordinates->toString())
                ->implode(' '),
        );
    }

    /**
     * @return positive-int
     */
    abstract public static function getAssociationCount(): int;
}
