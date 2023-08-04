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
    private array $cells;

    /**
     * @param Cell[] $cells
     */
    protected function __construct(array $cells)
    {
        Assert::count($cells, 9);

        usort($cells, static fn (Cell $a, Cell $b) => $a->coordinates->compare($b->coordinates));

        $this->cells = $cells;
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
                array_map(static fn (Cell $cell) => $cell->coordinates->toString(), $this->cells),
            ]);
        }

        return $v;
    }

    /**
     * @return FillableCell[]
     */
    public function getEmptyCells(): array
    {
        return array_filter($this->cells, static fn (Cell $cell) => $cell->isEmpty() && $cell instanceof FillableCell);
    }

    /**
     * @return CellValue[]
     */
    public function getPresentValues(): array
    {
        $cellsWithValue = array_filter($this->cells, static fn (Cell $cell) => ! $cell->isEmpty());

        return array_map(
            static fn (Cell $cell) => $cell->getCellValue(),
            array_values($cellsWithValue),
        );
    }

    public function getIterator(): Traversable
    {
        return new \ArrayIterator($this->cells);
    }
}
