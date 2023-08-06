<?php

declare(strict_types=1);

namespace Florian\SudokuSolver\Grid;

use Florian\SudokuSolver\Grid\Cell\Coordinates;
use Florian\SudokuSolver\Grid\Cell\FillableCell;
use Florian\SudokuSolver\Grid\Set\Column;
use Florian\SudokuSolver\Grid\Set\Region;
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
    /** @var Region[] */
    private array $regions;

    /**
     * @param Cell[] $cells
     */
    public function __construct(array $cells)
    {
        Assert::count($cells, Coordinates::MAX * Coordinates::MAX);

        $this->cells = $cells;
        [$this->columns, $this->rows, $this->regions] = $this->prepareSets($cells);
    }

    public function getCellByCoordinates(Coordinates $coordinates): Cell
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
     * @return iterable<Set>
     */
    public function getSetsOfCell(Cell $cell): iterable
    {
        yield $this->columns[$cell->coordinates->x];
        yield $this->rows[$cell->coordinates->y];
        yield $this->regions[$cell->regionNumber->value];
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

    /**
     * @param Cell[] $cells
     *
     * @return array{Column[], Row[], Region[]}
     */
    private function prepareSets(array $cells): array
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

        Assert::count($columns, Set::CELLS_COUNT);
        Assert::count($rows, Set::CELLS_COUNT);
        Assert::count($regions, Set::CELLS_COUNT);

        return [$columns, $rows, $regions];
    }
}
