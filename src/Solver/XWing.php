<?php

declare(strict_types=1);

namespace SudokuSolver\Solver;

use SudokuSolver\Grid\Cell;
use SudokuSolver\Grid\Cell\Coordinates;
use SudokuSolver\Grid\Cell\Value;
use SudokuSolver\Grid\Grid;
use SudokuSolver\Grid\Group;
use SudokuSolver\Grid\Group\Number\ColumnNumber;
use SudokuSolver\Grid\Group\Number\RowNumber;
use SudokuSolver\Solver\XWing\Direction;
use Webmozart\Assert\Assert;

final readonly class XWing
{
    /** @var ColumnNumber[] */
    public array $columnNumbers;
    /** @var RowNumber[] */
    public array $rowNumbers;

    /**
     * @param Coordinates[] $coordinatesList
     */
    public function __construct(
        public Direction $direction,
        public array $coordinatesList,
        public Value $value,
    ) {
        Assert::count($this->coordinatesList, 4);

        $columnNumbers = [];
        $rowNumbers = [];

        foreach ($this->coordinatesList as $coordinates) {
            $columnNumbers[] = ColumnNumber::fromCoordinates($coordinates);
            $rowNumbers[] = RowNumber::fromCoordinates($coordinates);
        }

        $this->columnNumbers = array_values(array_unique($columnNumbers));
        $this->rowNumbers = array_values(array_unique($rowNumbers));

        Assert::count($this->columnNumbers, 2);
        Assert::count($this->rowNumbers, 2);
    }

    public function contains(Cell $cell): bool
    {
        foreach ($this->coordinatesList as $coordinates) {
            if ($cell->coordinates->is($coordinates)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return Group[]
     */
    public function provideGroupsToModify(Grid $grid): array
    {
        return match ($this->direction) {
            Direction::Horizontal => array_map(
                static fn (ColumnNumber $n) => $grid->getColumn($n),
                $this->columnNumbers,
            ),
            Direction::Vertical => array_map(
                static fn (RowNumber $n) => $grid->getRow($n),
                $this->rowNumbers,
            ),
        };
    }

    public function toString(): string
    {
        return sprintf(
            '%s => %d => %s',
            $this->direction->name,
            $this->value->value,
            implode(' ', array_map(static fn (Coordinates $c) => $c->toString(), $this->coordinatesList)),
        );
    }
}
