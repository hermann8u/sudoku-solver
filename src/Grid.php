<?php

declare(strict_types=1);

namespace Sudoku;

use OutOfBoundsException;
use Sudoku\DataStructure\ArrayList;
use Sudoku\DataStructure\Map;
use Sudoku\Grid\Cell;
use Sudoku\Grid\Cell\Coordinates;
use Sudoku\Grid\Cell\FillableCell;
use Sudoku\Grid\Cell\Value;
use Sudoku\Grid\Group;
use Sudoku\Grid\Group\Column;
use Sudoku\Grid\Group\GroupNumber;
use Sudoku\Grid\Group\Number\ColumnNumber;
use Sudoku\Grid\Group\Number\RegionNumber;
use Sudoku\Grid\Group\Number\RowNumber;
use Sudoku\Grid\Group\Region;
use Sudoku\Grid\Group\Row;
use Webmozart\Assert\Assert;

final readonly class Grid
{
    public const CELLS_COUNT = 81;

    /** @var Map<ColumnNumber, Column> */
    public Map $columns;
    /** @var Map<RowNumber, Row> */
    public Map $rows;
    /** @var Map<RegionNumber, Region> */
    public Map $regions;

    /**
     * @param ArrayList<Cell> $cells
     */
    public function __construct(
        public ArrayList $cells,
    ) {
        Assert::count($this->cells, self::CELLS_COUNT);

        [$this->columns, $this->rows, $this->regions] = $this->prepareGroups($this->cells);
    }

    public function getCell(Coordinates $coordinates): Cell
    {
        return $this->cells
            ->findFirst(static fn (Cell $cell) => $cell->coordinates->equals($coordinates))
            ?? throw new OutOfBoundsException();
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
     * @return ArrayList<Group>
     */
    public function getGroupsForCell(Cell $cell): ArrayList
    {
        /** @var ArrayList<Group> */
        return ArrayList::fromItems(
            $this->getColumnByCell($cell),
            $this->getRowByCell($cell),
            $this->getRegionByCell($cell),
        );
    }

    public function getColumnByCell(Cell $cell): Column
    {
        return $this->columns->get($cell->getColumnNumber());
    }

    public function getRowByCell(Cell $cell): Row
    {
        return $this->rows->get($cell->getRowNumber());
    }

    public function getRegionByCell(Cell $cell): Region
    {
        return $this->regions->get($cell->getRegionNumber());
    }

    public function isSolved(): bool
    {
        return $this->isFilled() && $this->containsDuplicate() === false;
    }

    public function isFilled(): bool
    {
        return ! $this->cells->exists(static fn (Cell $cell) => $cell->isEmpty());
    }

    public function containsDuplicate(): bool
    {
        foreach ([$this->columns, $this->rows, $this->regions] as $groups) {
            /** @var Group $group */
            foreach ($groups as $group) {
                if ($group->containsDuplicate()) {
                    return true;
                }
            }
        }

        return false;
    }

    public function withUpdatedCell(FillableCell $cell): self
    {
        $cells = $this->cells->filter($cell->isNot(...));

        return new self($cells->with($cell));
    }

    public function toString(): string
    {
        return $this->cells
            ->map(static fn (Cell $cell) => $cell->value . ($cell->coordinates->x === Coordinates::MAX ? PHP_EOL : ';'))
            ->implode();
    }

    /**
     * @param ArrayList<Cell> $cells
     *
     * @return array{
     *     Map<ColumnNumber, Column>,
     *     Map<RowNumber, Row>,
     *     Map<RegionNumber, Region>,
     * }
     */
    private function prepareGroups(ArrayList $cells): array
    {
        /** @var Map<ColumnNumber, Column> $columns */
        $columns = $this->createGroupsOfType($cells, Column::class);
        /** @var Map<RowNumber, Row> $rows */
        $rows = $this->createGroupsOfType($cells, Row::class);
        /** @var Map<RegionNumber, Region> $regions */
        $regions = $this->createGroupsOfType($cells, Region::class);

        return [$columns, $rows, $regions];
    }

    /**
     * @template T of Group
     *
     * @param ArrayList<Cell> $cells
     * @param class-string<T> $groupType
     *
     * @return Map<GroupNumber, T>
     */
    private function createGroupsOfType(ArrayList $cells, string $groupType): Map
    {
        $groupNumberType = $groupType::getNumberType();

        $groupNumbers = $cells
            ->map(static fn (Cell $cell) => $groupNumberType::fromCell($cell))
            ->unique(static fn (GroupNumber $a, GroupNumber $b) => $a->value === $b->value)
            ->sorted(static fn (GroupNumber $a, GroupNumber $b) => $a->value <=> $b->value);

        $groupsTuples = $groupNumbers->map(static fn (GroupNumber $number) => [
            $number,
            new $groupType(
                $number,
                $cells->filter(static fn (Cell $cell) => $number::fromCell($cell)->equals($number)),
            ),
        ])->toArray();

        $groups = Map::fromTuples($groupsTuples);

        Assert::count($groups, Group::CELLS_COUNT);

        return $groups;
    }
}
