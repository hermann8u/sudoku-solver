<?php

declare(strict_types=1);

namespace SudokuSolver\Grid;

use SudokuSolver\DataStructure\ArrayList;
use SudokuSolver\Grid\Cell\FillableCell;
use SudokuSolver\Grid\Cell\Value;
use SudokuSolver\Grid\Group\GroupNumber;
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

        return $presentValues->count() !== $presentValues->map(static fn (Value $v) => $v->value)->unique()->count();
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
