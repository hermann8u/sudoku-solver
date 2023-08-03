<?php

declare(strict_types=1);

namespace Florian\SudokuSolver\Grid;

use Florian\SudokuSolver\Grid\Cell\FillableCell;
use Florian\SudokuSolver\Grid\Set\Column;
use Florian\SudokuSolver\Grid\Set\Group;
use Florian\SudokuSolver\Grid\Set\Row;
use Webmozart\Assert\Assert;

final readonly class Grid
{
    /** @var Cell[] */
    private array $cells;
    /** @var Column[] */
    private array $columns;
    /** @var Row[] */
    private array $rows;
    /** @var Group[] */
    private array $groups;

    /**
     * @param Cell[] $cells
     */
    public function __construct(array $cells)
    {
        $expectedCellsCount = 9 * 9;
        Assert::count($cells, $expectedCellsCount);

        $this->cells = $cells;
        [$this->columns, $this->rows, $this->groups] = $this->prepareSets($cells);
    }

    /**
     * @return FillableCell[]
     */
    public function getFillableCells(): array
    {
        return array_values(array_filter(
            $this->cells,
            static fn (Cell $cell) => $cell instanceof FillableCell,
        ));
    }

    /**
     * @return iterable<Set>
     */
    public function getSetsContainingCell(Cell $cell): iterable
    {
        yield $this->columns[$cell->coordinates->x];
        yield $this->rows[$cell->coordinates->y];
        yield $this->groups[$cell->groupNumber->value];
    }

    public function getCellsByRow(int $y): Set
    {
        return $this->rows[$y];
    }

    public function isValid(): bool
    {
        return $this->isFilled() && $this->containsDuplicate() === false;
    }

    public function isFilled(): bool
    {
        foreach ($this->cells as $cell) {
            if ($cell->isEmpty()) {
                return false;
            }
        }

        return true;
    }

    public function containsDuplicate(): bool
    {
        foreach ($this->columns as $column) {
            if (! $column->containsDuplicate()) {
                return false;
            }
        }

        foreach ($this->rows as $row) {
            if (! $row->containsDuplicate()) {
                return false;
            }
        }

        foreach ($this->groups as $group) {
            if (! $group->containsDuplicate()) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param Cell[] $cells
     *
     * @return array{Column[], Row[], Group[]}
     */
    private function prepareSets(array $cells): array
    {
        $columns = [];
        $rows = [];
        $groups = [];

        foreach ($cells as $cell) {
            $x = $cell->coordinates->x;
            if (! isset($columns[$x])) {
                $columns[$x] = Column::fromCells($cells, $x);
            }

            $y = $cell->coordinates->y;
            if (! isset($rows[$y])) {
                $rows[$y] = Row::fromCells($cells, $y);
            }

            $groupNumber = $cell->groupNumber;
            if (! isset($groups[$groupNumber->value])) {
                $groups[$groupNumber->value] = Group::fromCells($cells, $groupNumber);
            }
        }

        Assert::count($columns, 9);
        Assert::count($rows, 9);
        Assert::count($groups, 9);

        return [$columns, $rows, $groups];
    }
}
