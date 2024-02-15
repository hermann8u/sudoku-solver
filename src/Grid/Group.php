<?php

declare(strict_types=1);

namespace Sudoku\Grid;

use Sudoku\DataStructure\ArrayList;
use Sudoku\Grid\Cell\FillableCell;
use Sudoku\Grid\Cell\Value;
use Sudoku\Grid\Group\GroupNumber;
use Webmozart\Assert\Assert;

/**
 * @template TGroupNumber of GroupNumber
 */
abstract readonly class Group
{
    public const CELLS_COUNT = 9;

    /** @var ArrayList<Cell> */
    public ArrayList $cells;

    /**
     * @param TGroupNumber $number
     * @param ArrayList<Cell> $cells
     */
    public function __construct(
        public GroupNumber $number,
        ArrayList $cells,
    ) {
        Assert::count($cells, self::CELLS_COUNT);
        Assert::allTrue($cells->map(static fn (Cell $cell) => $number::fromCell($cell)->equals($number)));

        $this->cells = $cells->sorted(static fn (Cell $a, Cell $b) => $a->coordinates->compare($b->coordinates));
    }

    /**
     * @return class-string<TGroupNumber>
     */
    abstract public static function getNumberType(): string;

    public function isValid(): bool
    {
        return $this->getEmptyCells()->isEmpty() && $this->containsDuplicate() === false;
    }

    public function containsDuplicate(): bool
    {
        $presentValues = $this->getPresentValues();
        $uniqueValues = $presentValues->unique(static fn (Value $a, Value $b) => $a->equals($b));

        return $presentValues->count() !== $uniqueValues->count();
    }

    /**
     * @return ArrayList<FillableCell>
     */
    public function getEmptyCells(): ArrayList
    {
        /** @var ArrayList<FillableCell> $emptyCells */
        $emptyCells = $this->cells->filter(static fn (Cell $cell) => $cell->isEmpty() && $cell instanceof FillableCell);

        return $emptyCells;
    }

    /**
     * @return ArrayList<FillableCell>
     */
    public function getEmptyCellsInGroup(Group $group): ArrayList
    {
        return $this->getEmptyCells()->filter(static fn (FillableCell $c) => $group->cells->exists($c->is(...)));
    }

    /**
     * @return ArrayList<FillableCell>
     */
    public function getEmptyCellsNotInGroup(Group $group): ArrayList
    {
        return $this->getEmptyCells()->filter(static fn (FillableCell $c) => ! $group->cells->exists($c->is(...)));
    }

    /**
     * @return ArrayList<Value>
     */
    public function getPresentValues(): ArrayList
    {
        /** @var ArrayList<Value> $values */
        $values = $this->cells
            ->map(static fn (Cell $cell) => $cell->value)
            ->filter(static fn (?Value $value) => $value instanceof Value);

        return $values;
    }
}
