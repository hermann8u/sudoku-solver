<?php

declare(strict_types=1);

namespace SudokuSolver\Solver;

use SudokuSolver\DataStructure\ArrayList;
use SudokuSolver\Grid\Cell;
use SudokuSolver\Grid\Cell\Coordinates;
use SudokuSolver\Grid\Cell\Value;
use SudokuSolver\Grid\Grid;
use SudokuSolver\Grid\Group\Column;
use SudokuSolver\Grid\Group\GroupNumber;
use SudokuSolver\Grid\Group\Number\ColumnNumber;
use SudokuSolver\Grid\Group\Number\RowNumber;
use SudokuSolver\Grid\Group\Row;
use SudokuSolver\Solver\XWing\Direction;
use Webmozart\Assert\Assert;

final readonly class XWing
{
    /** @var ArrayList<ColumnNumber> */
    public ArrayList $columnNumbers;
    /** @var ArrayList<RowNumber> */
    public ArrayList $rowNumbers;

    /**
     * @param ArrayList<Coordinates> $coordinatesList
     */
    public function __construct(
        public Direction $direction,
        public ArrayList $coordinatesList,
        public Value $value,
    ) {
        Assert::count($this->coordinatesList, 4);

        $uniqueCallable = static fn (GroupNumber $a, GroupNumber $b) => $a->value === $b->value;

        $this->columnNumbers = $this->coordinatesList
            ->map(static fn (Coordinates $coordinates) => ColumnNumber::fromCoordinates($coordinates))
            ->unique($uniqueCallable);

        $this->rowNumbers = $this->coordinatesList
            ->map(static fn (Coordinates $coordinates) => RowNumber::fromCoordinates($coordinates))
            ->unique($uniqueCallable);

        Assert::count($this->columnNumbers, 2);
        Assert::count($this->rowNumbers, 2);
    }

    public function contains(Cell $cell): bool
    {
        foreach ($this->coordinatesList as $coordinates) {
            if ($cell->coordinates->equals($coordinates)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return ArrayList<Column>|ArrayList<Row>
     */
    public function getGroupsToModify(Grid $grid): ArrayList
    {
        return match ($this->direction) {
            Direction::Horizontal => $this->columnNumbers->map(
                static fn (ColumnNumber $n) => $grid->columns->get($n),
            ),
            Direction::Vertical => $this->rowNumbers->map(
                static fn (RowNumber $n) => $grid->rows->get($n),
            ),
        };
    }

    public function toString(): string
    {
        $coordinatesStrings = $this->coordinatesList->map(static fn (Coordinates $c) => $c->toString())->toArray();

        return sprintf(
            '%s => %d => %s',
            $this->direction->name,
            $this->value->value,
            implode(' ', $coordinatesStrings),
        );
    }
}
