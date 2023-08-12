<?php

declare(strict_types=1);

namespace SudokuSolver\Grid\Group;

use SudokuSolver\Grid\Cell;
use SudokuSolver\Grid\Cell\Coordinates;
use SudokuSolver\Grid\Group;
use Webmozart\Assert\Assert;

final readonly class Column extends Group
{
    /**
     * @param Cell[] $cells
     * @param int<Coordinates::MIN, Coordinates::MAX> $number
     */
    private function __construct(
        array $cells,
        public int $number,
    ) {
        Assert::greaterThanEq($this->number, Coordinates::MIN);
        Assert::lessThanEq($this->number, Coordinates::MAX);

        parent::__construct($cells);
    }

    /**
     * @param Cell[] $cells
     * @param int<Coordinates::MIN, Coordinates::MAX> $x
     */
    public static function fromCells(array $cells, int $x): self
    {
        return new self(
            array_values(array_filter($cells, static fn (Cell $cell) => $cell->coordinates->x === $x)),
            $x,
        );
    }
}
