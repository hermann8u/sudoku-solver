<?php

declare(strict_types=1);

namespace SudokuSolver\Grid;

use SudokuSolver\DataStructure\ArrayList;
use SudokuSolver\DataStructure\Map;
use SudokuSolver\Grid\Cell\Coordinates;
use SudokuSolver\Grid\Cell\FillableCell;
use SudokuSolver\Grid\Cell\Value;
use SudokuSolver\Grid\Group\Column;
use SudokuSolver\Grid\Group\GroupNumber;
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
     * @param ArrayList<Cell> $cells
     */
    public function __construct(
        public ArrayList $cells,
    ) {
        Assert::count($this->cells, Coordinates::MAX * Coordinates::MAX);

        [$this->columns, $this->rows, $this->regions] = $this->prepareGroups($this->cells);
    }

    public function getCell(Coordinates $coordinates): Cell
    {
        return $this->cells
            ->findFirst(static fn (Cell $cell) => $cell->coordinates->equals($coordinates))
            ?? throw new \OutOfBoundsException();
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
        return ! $this->cells->exists(static fn (Cell $cell) => $cell->isEmpty());
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
        $cells = $this->cells->filter(static fn (Cell $cell) => ! $cell->coordinates->equals($coordinates));

        return new self($cells->merge(new FillableCell($coordinates, $value)));
    }

    public function toString(): string
    {
        $cellStrings = $this->cells
            ->map(static fn (Cell $cell) => $cell->value . ($cell->coordinates->x === Coordinates::MAX ? PHP_EOL : ';'))
            ->toArray();

        return implode('', $cellStrings);
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

        /** @var ArrayList<GroupNumber> $groupNumbers */
        $groupNumbers = $cells->map(static fn (Cell $cell) => $groupNumberType::fromCell($cell));

        $alreadyPresent = [];
        $groupNumbers = $groupNumbers
            ->filter(static function (GroupNumber $number) use (&$alreadyPresent) {
                if (\in_array($number->value, $alreadyPresent, true)) {
                    return false;
                }

                $alreadyPresent[] = $number->value;

                return true;
            })
            ->sorted(static fn (GroupNumber $a, GroupNumber $b) => $a->value <=> $b->value)
        ;

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
