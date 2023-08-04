<?php

declare(strict_types=1);

namespace Florian\SudokuSolver\Grid\Set;

use Florian\SudokuSolver\Grid\Cell;
use Florian\SudokuSolver\Grid\Cell\Coordinates;
use Florian\SudokuSolver\Grid\Set;
use Webmozart\Assert\Assert;

final readonly class Column extends Set
{
    /**
     * @param Cell[] $cells
     * @param int<Coordinates::MIN, Coordinates::MAX> $x
     */
    private function __construct(
        array $cells,
        public int $x,
    ) {
        Assert::greaterThanEq($this->x, Coordinates::MIN);
        Assert::lessThanEq($this->x, Coordinates::MAX);

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
