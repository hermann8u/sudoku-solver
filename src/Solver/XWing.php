<?php

declare(strict_types=1);

namespace SudokuSolver\Solver;

use SudokuSolver\Grid\Cell;
use SudokuSolver\Grid\Cell\Value;
use SudokuSolver\Grid\Cell\Coordinates;
use SudokuSolver\Solver\XWing\Direction;
use Webmozart\Assert\Assert;

final readonly class XWing
{
    /** @var int<Coordinates::MIN, Coordinates::MAX>[] */
    public array $columnNumbers;
    /** @var int<Coordinates::MIN, Coordinates::MAX>[] */
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
            $columnNumbers[$coordinates->x] = 0;
            $rowNumbers[$coordinates->y] = 0;
        }

        $this->columnNumbers = \array_keys($columnNumbers);
        $this->rowNumbers = \array_keys($rowNumbers);

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
     * @return int<Coordinates::MIN, Coordinates::MAX>[]
     */
    public function getGroupToModifyNumbers(): array
    {
        return match ($this->direction) {
            Direction::Horizontal => $this->columnNumbers,
            Direction::Vertical => $this->rowNumbers,
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
