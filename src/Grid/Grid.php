<?php

declare(strict_types=1);

namespace SudokuSolver\Grid;

use SudokuSolver\DataStructure\Map;
use SudokuSolver\Grid\Cell\Coordinates;
use SudokuSolver\Grid\Cell\FillableCell;
use SudokuSolver\Grid\Cell\Value;
use SudokuSolver\Grid\Group\Column;
use SudokuSolver\Grid\Group\Number\ColumnNumber;
use SudokuSolver\Grid\Group\Number\RegionNumber;
use SudokuSolver\Grid\Group\Number\RowNumber;
use SudokuSolver\Grid\Group\Region;
use SudokuSolver\Grid\Group\Row;
use Webmozart\Assert\Assert;

final readonly class Grid
{
    /** @var Map<ColumnNumber, Column> */
    public Map $columns;
    /** @var Map<RowNumber, Row> */
    public Map $rows;
    /** @var Map<RegionNumber, Region> */
    public Map $regions;

    /**
     * @param Cell[] $cells
     */
    public function __construct(
        private array $cells,
    ) {
        Assert::count($this->cells, Coordinates::MAX * Coordinates::MAX);

        [$this->columns, $this->rows, $this->regions] = $this->prepareGroups($this->cells);
    }

    public function getCell(Coordinates $coordinates): Cell
    {
        foreach ($this->cells as $cell) {
            if ($cell->coordinates->equals($coordinates)) {
                return $cell;
            }
        }

        throw new \DomainException();
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
     * @return FillableCell[]
     */
    public function getEmptyCells(): array
    {
        return array_values(array_filter(
            $this->cells,
            static fn (Cell $cell) => $cell->isEmpty() && $cell instanceof FillableCell,
        ));
    }

    /**
     * @return iterable<Group>
     */
    public function getGroupsForCell(Cell $cell): iterable
    {
        yield $this->getColumnByCell($cell);
        yield $this->getRowByCell($cell);
        yield $this->regions->get(RegionNumber::fromCell($cell));
    }

    public function getColumnByCell(Cell $cell): Column
    {
        return $this->columns->get(ColumnNumber::fromCell($cell));
    }

    public function getRowByCell(Cell $cell): Row
    {
        return $this->rows->get(RowNumber::fromCell($cell));
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
        foreach ([$this->columns, $this->rows, $this->regions] as $groups) {
            foreach ($groups as $group) {
                if ($group->containsDuplicate()) {
                    return true;
                }
            }
        }

        return false;
    }

    public function withUpdatedCell(Coordinates $coordinates, Value $value): self
    {
        foreach ($this->cells as $key => $cell) {
            if (! $cell->coordinates->equals($coordinates)) {
                continue;
            }

            if (! $cell instanceof FillableCell) {
                throw new \LogicException();
            }

            $cells = $this->cells;

            $cells[$key] = new FillableCell($coordinates, $value);

            return new self($cells);
        }

        throw new \DomainException();
    }

    public function toString(): string
    {
        $string = '';

        foreach ($this->cells as $cell) {
            $string .= $cell->value . ($cell->coordinates->x === Coordinates::MAX ? PHP_EOL : ';');
        }

        return $string;
    }

    /**
     * @param Cell[] $cells
     *
     * @return array{
     *     Map<ColumnNumber, Column>,
     *     Map<RowNumber, Row>,
     *     Map<RegionNumber, Region>,
     * }
     */
    private function prepareGroups(array $cells): array
    {
        /** @var Map<ColumnNumber, Column> $columns */
        $columns = Map::empty();

        /** @var Map<RowNumber, Row> $rows */
        $rows = Map::empty();

        /** @var Map<RegionNumber, Region> $regions */
        $regions = Map::empty();

        foreach ($cells as $cell) {
            $columnNumber = ColumnNumber::fromCell($cell);
            $rowNumber = RowNumber::fromCell($cell);
            $regionNumber = RegionNumber::fromCell($cell);

            if (! $columns->has($columnNumber)) {
                $columns = $columns->with($columnNumber, Column::fromAllCells($cells, $columnNumber));
            }

            if (! $rows->has($rowNumber)) {
                $rows = $rows->with($rowNumber, Row::fromAllCells($cells, $rowNumber));
            }

            if (! $regions->has($regionNumber)) {
                $regions = $regions->with($regionNumber, Region::fromAllCells($cells, $regionNumber));
            }
        }

        Assert::count($columns, Group::CELLS_COUNT);
        Assert::count($rows, Group::CELLS_COUNT);
        Assert::count($regions, Group::CELLS_COUNT);

        return [$columns, $rows, $regions];
    }
}
