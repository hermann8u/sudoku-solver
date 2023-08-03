<?php

declare(strict_types=1);

namespace Florian\SudokuSolver\Grid;

use Florian\SudokuSolver\Grid\Cell\CellValue;
use Florian\SudokuSolver\Grid\Cell\FillableCell;
use Traversable;
use Webmozart\Assert\Assert;

/**
 * @implements \IteratorAggregate<int, Cell>
 */
abstract readonly class Set implements \IteratorAggregate
{
    /** @var Cell[] */
    private array $set;

    /**
     * @param Cell[] $set
     */
    protected function __construct(array $set)
    {
        Assert::count($set, 9);

        usort($set, static fn (Cell $a, Cell $b) => $a->coordinates->compare($b->coordinates));

        $this->set = $set;
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

        $v = count($presentValues) !== count(array_unique(array_column($presentValues, 'value')));

        if ($v === true) {
            dd([
                'Contains duplicates :o',
                $presentValues,
                array_map(static fn(Cell $cell) => $cell->coordinates->toString(), $this->set)
            ]);
        }

        return $v;
    }

    /**
     * @return FillableCell[]
     */
    public function getEmptyCells(): array
    {
        return array_filter($this->set, static fn (Cell $cell) => $cell->isEmpty() && $cell instanceof FillableCell);
    }

    /**
     * @return CellValue[]
     */
    public function getPresentValues(): array
    {
        $cellsWithValue = array_filter($this->set, static fn (Cell $cell) => ! $cell->isEmpty());

        return array_map(
            static fn (Cell $cell) => $cell->getCellValue(),
            array_values($cellsWithValue),
        );
    }

    /**
     * @return Cell[]
     */
    public function getCellsOnSameColumn(Cell $cell): array
    {
        return array_values(array_filter($this->set, $cell->isOnSameColumn(...)));
    }

    /**
     * @return Cell[]
     */
    public function getCellsOnSameRow(Cell $cell): array
    {
        return array_values(array_filter($this->set, $cell->isOnSameRow(...)));
    }

    /**
     * @return Cell[]
     */
    public function getCellsInSameGroup(Cell $cell): array
    {
        return array_values(array_filter($this->set, $cell->isInSameGroup(...)));
    }

    public function getIterator(): Traversable
    {
        return new \ArrayIterator($this->set);
    }
}
