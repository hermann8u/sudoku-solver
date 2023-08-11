<?php

declare(strict_types=1);

namespace SudokuSolver\Grid;

use SudokuSolver\Grid\Cell\Coordinates;
use SudokuSolver\Grid\Cell\FillableCell;
use SudokuSolver\Grid\Group\Column;
use SudokuSolver\Grid\Group\Region;
use SudokuSolver\Grid\Group\RegionNumber;
use SudokuSolver\Grid\Group\Row;
use Webmozart\Assert\Assert;

final readonly class Grid
{
    /** @var Cell[] */
    private array $cells;
    /** @var array<int<Coordinates::MIN, Coordinates::MAX>, Column> */
    public array $columns;
    /** @var array<int<Coordinates::MIN, Coordinates::MAX>, Row> */
    public array $rows;
    /** @var array<int<Coordinates::MIN, Coordinates::MAX>, Region> */
    private array $regions;

    /**
     * @param Cell[] $cells
     */
    public function __construct(array $cells)
    {
        Assert::count($cells, Coordinates::MAX * Coordinates::MAX);

        $this->cells = $cells;
        [$this->columns, $this->rows, $this->regions] = $this->prepareGroups($cells);
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
     * @return iterable<Group>
     */
    public function getGroupForCell(Cell $cell): iterable
    {
        yield $this->getColumnByCell($cell);
        yield $this->getRowByCell($cell);
        yield $this->getRegionByCell($cell);
    }

    public function getRowByCell(Cell $cell): Row
    {
        return $this->rows[$cell->coordinates->y];
    }

    public function getColumnByCell(Cell $cell): Column
    {
        return $this->columns[$cell->coordinates->x];
    }

    public function getRegionByCell(Cell $cell): Region
    {
        return $this->regions[$cell->regionNumber->value];
    }

    public function getCellsByRow(int $y): Group
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
            if ($column->containsDuplicate()) {
                return true;
            }
        }

        foreach ($this->rows as $row) {
            if ($row->containsDuplicate()) {
                return true;
            }
        }

        foreach ($this->regions as $region) {
            if ($region->containsDuplicate()) {
                return true;
            }
        }

        return false;
    }

    public function toString(): string
    {
        $string = '';

        foreach ($this->cells as $cell) {
            $string .= $cell->getCellValue()->value . ($cell->coordinates->x === 9 ? PHP_EOL : ';');
        }

        return $string;
    }

    /**
     * @param Cell[] $cells
     *
     * @return array{
     *     array<int<Coordinates::MIN, Coordinates::MAX>, Column>,
     *     array<int<Coordinates::MIN, Coordinates::MAX>, Row>,
     *     array<int<RegionNumber::MIN, RegionNumber::MAX>, Region>,
     * }
     */
    private function prepareGroups(array $cells): array
    {
        $columns = [];
        $rows = [];
        $regions = [];

        foreach ($cells as $cell) {
            $x = $cell->coordinates->x;
            if (! isset($columns[$x])) {
                $columns[$x] = Column::fromCells($cells, $x);
            }

            $y = $cell->coordinates->y;
            if (! isset($rows[$y])) {
                $rows[$y] = Row::fromCells($cells, $y);
            }

            $regionNumber = $cell->regionNumber;
            if (! isset($regions[$regionNumber->value])) {
                $regions[$regionNumber->value] = Region::fromCells($cells, $regionNumber);
            }
        }

        Assert::count($columns, Group::CELLS_COUNT);
        Assert::count($rows, Group::CELLS_COUNT);
        Assert::count($regions, Group::CELLS_COUNT);

        return [$columns, $rows, $regions];
    }
}
