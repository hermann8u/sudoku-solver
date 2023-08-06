<?php

declare(strict_types=1);

namespace Florian\SudokuSolver\Solver;

use Florian\SudokuSolver\Grid\Cell;
use Florian\SudokuSolver\Grid\Cell\Coordinates;
use Webmozart\Assert\Assert;

final class Triplet
{
    /**
     * @param Coordinates[] $coordinatesTriplet
     */
    public function __construct(
        public array $coordinatesTriplet,
        public Candidates $candidates,
    ) {
        Assert::count($this->coordinatesTriplet, 3);
        Assert::count($this->candidates->values, 3);
    }

    public function contains(Cell $cell): bool
    {
        return in_array(
            $cell->coordinates->toString(),
            array_map(static fn (Coordinates $c) => (string) $c, $this->coordinatesTriplet),
            true,
        );
    }
}
