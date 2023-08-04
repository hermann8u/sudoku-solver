<?php

declare(strict_types=1);

namespace Florian\SudokuSolver\Grid\Set;

use Florian\SudokuSolver\Grid\Cell;
use Florian\SudokuSolver\Grid\Cell\Coordinates;
use Florian\SudokuSolver\Grid\Set;
use Webmozart\Assert\Assert;

final readonly class Row extends Set
{
    /**
     * @param Cell[] $cells
     * @param int<Coordinates::MIN, Coordinates::MAX> $y
     */
    private function __construct(
        array $cells,
        public int $y,
    ) {
        Assert::greaterThanEq($this->y, Coordinates::MIN);
        Assert::lessThanEq($this->y, Coordinates::MAX);

        parent::__construct($cells);
    }

    /**
     * @param Cell[] $cells
     * @param int<Coordinates::MIN, Coordinates::MAX> $y
     */
    public static function fromCells(array $cells, int $y): self
    {
        return new self(
            array_values(array_filter($cells, static fn (Cell $cell) => $cell->coordinates->y === $y)),
            $y,
        );
    }
}
