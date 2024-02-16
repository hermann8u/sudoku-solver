<?php

declare(strict_types=1);

namespace Sudoku\Solver\Association;

use Sudoku\DataStructure\ArrayList;
use Sudoku\Grid;
use Sudoku\Grid\Cell\FillableCell;
use Sudoku\Grid\Cell\Value;
use Sudoku\Grid\Group;
use Sudoku\Grid\Group\Column;
use Sudoku\Grid\Group\Region;
use Sudoku\Grid\Group\Row;
use Sudoku\Solver\Association;
use Webmozart\Assert\Assert;

final readonly class PointingPair implements Association
{
    public const COUNT = 2;

    /**
     * @param ArrayList<FillableCell> $cells
     */
    public function __construct(
        public Group $group,
        public Group $pointingOn,
        public ArrayList $cells,
        public Value $value,
    )
    {
        if ($this->group instanceof Region) {
            Assert::true($this->pointingOn instanceof Row || $this->pointingOn instanceof Column);
        } else {
            Assert::isInstanceOf($this->pointingOn, Region::class);
        }

        Assert::count($this->cells, self::COUNT);
    }

    public function getTargetedCells(Grid $grid): ArrayList
    {
        return $this->pointingOn->getEmptyCellsNotInGroup($this->group);
    }

    public function getCandidatesToEliminate(): ArrayList
    {
        return ArrayList::fromItems($this->value);
    }

    public function toString(): string
    {
        return sprintf(
            '%d => %s',
            $this->value->value,
            $this->cells
                ->map(static fn (FillableCell $c) => $c->coordinates->toString())
                ->implode(' '),
        );
    }
}
