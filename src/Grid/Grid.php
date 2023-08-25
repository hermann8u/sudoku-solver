<?php

declare(strict_types=1);

namespace SudokuSolver\Grid;

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
    /** @var array<int<Coordinates::MIN, Coordinates::MAX>, Column> */
    public array $columns;
    /** @var array<int<Coordinates::MIN, Coordinates::MAX>, Row> */
    public array $rows;
    /** @var array<int<Coordinates::MIN, Coordinates::MAX>, Region> */
    public array $regions;

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
            if ($cell->coordinates->is($coordinates)) {
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
        yield $this->getRegionByCell($cell);
    }

    public function getColumnByCell(Cell $cell): Column
    {
        return $this->getColumn(ColumnNumber::fromCell($cell));
    }

    public function getRowByCell(Cell $cell): Row
    {
        return $this->getRow(RowNumber::fromCell($cell));
    }

    public function getRegionByCell(Cell $cell): Region
    {
        return $this->getRegion(RegionNumber::fromCell($cell));
    }

    public function getColumn(ColumnNumber $number): Column
    {
        return $this->columns[$number->value];
    }

    public function getRow(RowNumber $number): Row
    {
        return $this->rows[$number->value];
    }

    public function getRegion(RegionNumber $number): Region
    {
        return $this->regions[$number->value];
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
        /** @var Group[] $groups */
        $groups = [...$this->columns, ...$this->rows, ...$this->regions];

        foreach ($groups as $group) {
            if ($group->containsDuplicate()) {
                return true;
            }
        }

        return false;
    }

    public function withUpdatedCell(Coordinates $coordinates, Value $value): self
    {
        foreach ($this->cells as $key => $cell) {
            if (! $cell->coordinates->is($coordinates)) {
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
     *     array<int<ColumnNumber::MIN, ColumnNumber::MAX>, Column>,
     *     array<int<RowNumber::MIN, RowNumber::MAX>, Row>,
     *     array<int<RegionNumber::MIN, RegionNumber::MAX>, Region>,
     * }
     */
    private function prepareGroups(array $cells): array
    {
        $columns = [];
        $rows = [];
        $regions = [];

        foreach ($cells as $cell) {
            $columnNumber = ColumnNumber::fromCell($cell);
            if (! isset($columns[$columnNumber->value])) {
                $columns[$columnNumber->value] = Column::fromAllCells($cells, $columnNumber);
            }

            $rowNumber = RowNumber::fromCell($cell);
            if (! isset($rows[$rowNumber->value])) {
                $rows[$rowNumber->value] = Row::fromAllCells($cells, $rowNumber);
            }

            $regionNumber = RegionNumber::fromCell($cell);
            if (! isset($regions[$regionNumber->value])) {
                $regions[$regionNumber->value] = Region::fromAllCells($cells, $regionNumber);
            }
        }

        Assert::count($columns, Group::CELLS_COUNT);
        Assert::count($rows, Group::CELLS_COUNT);
        Assert::count($regions, Group::CELLS_COUNT);

        return [$columns, $rows, $regions];
    }
}
