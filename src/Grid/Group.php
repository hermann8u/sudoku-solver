<?php

declare(strict_types=1);

namespace SudokuSolver\Grid;

use SudokuSolver\Grid\Cell\Value;
use SudokuSolver\Grid\Cell\FillableCell;
use SudokuSolver\Grid\Group\Number;
use Traversable;
use Webmozart\Assert\Assert;

/**
 * @implements \IteratorAggregate<int, Cell>
 */
abstract readonly class Group implements \IteratorAggregate
{
    public const CELLS_COUNT = 9;

    /** @var Cell[] */
    public array $cells;
    public Number $number;

    /**
     * @param Cell[] $cells
     */
    protected function __construct(array $cells, Number $number)
    {
        Assert::count($cells, self::CELLS_COUNT);

        usort($cells, static fn (Cell $a, Cell $b) => $a->coordinates->compare($b->coordinates));

        $this->cells = $cells;
        $this->number = $number;
    }

    public function isValid(): bool
    {
        return $this->isFilled() && $this->containsDuplicate() === false;
    }

    public function isFilled(): bool
    {
        return count($this->getEmptyCells()) === 0;
    }

    public function containsDuplicate(): bool
    {
        $presentValues = $this->getPresentValues();

        return count($presentValues) !== count(array_unique(array_column($presentValues, 'value')));
    }

    /**
     * @return FillableCell[]
     */
    public function getEmptyCells(): array
    {
        return array_filter($this->cells, static fn (Cell $cell) => $cell->isEmpty() && $cell instanceof FillableCell);
    }

    /**
     * @return Value[]
     */
    public function getPresentValues(): array
    {
        $cellsWithValue = array_filter($this->cells, static fn (Cell $cell) => ! $cell->isEmpty());

        return array_map(
            static fn (Cell $cell) => $cell->value,
            array_values($cellsWithValue),
        );
    }

    public function getIterator(): Traversable
    {
        return new \ArrayIterator($this->cells);
    }
}
